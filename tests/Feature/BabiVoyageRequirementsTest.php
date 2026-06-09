<?php

namespace Tests\Feature;

use App\Models\Airline;
use App\Models\Airport;
use App\Models\Flight;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class BabiVoyageRequirementsTest extends TestCase
{
    use RefreshDatabase;

    public function test_auth_profile_and_logout_flow(): void
    {
        $registerResponse = $this->postJson('/api/register', [
            'name' => 'Mediane Ozeir',
            'email' => 'mediane@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'phone' => '12345678',
        ]);

        $registerResponse
            ->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['user', 'token']]);

        $loginResponse = $this->postJson('/api/login', [
            'email' => 'mediane@example.com',
            'password' => 'password123',
        ]);

        $loginResponse
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonStructure(['data' => ['user', 'token']]);

        $user = User::where('email', 'mediane@example.com')->firstOrFail();
        Sanctum::actingAs($user);

        $this->getJson('/api/profile')
            ->assertOk()
            ->assertJsonPath('data.email', 'mediane@example.com');

        $this->putJson('/api/profile', ['name' => 'Updated Name'])
            ->assertOk()
            ->assertJsonPath('data.name', 'Updated Name');

        $this->postJson('/api/logout')
            ->assertOk()
            ->assertJsonPath('success', true);
    }

    public function test_only_admins_can_delete_flights(): void
    {
        $flight = $this->createFlight();

        Sanctum::actingAs($this->createUser('user'));

        $this->deleteJson("/api/admin/flights/{$flight->id}")
            ->assertForbidden()
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('flights', ['id' => $flight->id]);

        Sanctum::actingAs($this->createUser('admin'));

        $this->deleteJson("/api/admin/flights/{$flight->id}")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertDatabaseMissing('flights', ['id' => $flight->id]);
    }

    public function test_flight_listing_is_paginated(): void
    {
        $this->createFlight(['flight_number' => 'BV-101']);
        $this->createFlight(['flight_number' => 'BV-102']);

        $this->getJson('/api/flights?per_page=1')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('meta.per_page', 1)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_flight_update_validation_uses_existing_flight_state(): void
    {
        $flight = $this->createFlight([
            'total_seats' => 100,
            'available_seats' => 80,
        ]);

        Sanctum::actingAs($this->createUser('admin'));

        $this->putJson("/api/admin/flights/{$flight->id}", [
            'available_seats' => 101,
        ])->assertUnprocessable()
            ->assertJsonValidationErrors('available_seats');
    }

    public function test_api_validation_errors_use_standard_json_shape(): void
    {
        $this->postJson('/api/register', [
            'name' => '',
            'email' => 'not-an-email',
            'password' => 'short',
            'password_confirmation' => 'different',
        ])->assertUnprocessable()
            ->assertJsonPath('success', false)
            ->assertJsonPath('message', 'Validation failed.')
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    public function test_booking_passenger_and_saved_flight_flows(): void
    {
        $user = $this->createUser('user');
        $flight = $this->createFlight([
            'total_seats' => 10,
            'available_seats' => 10,
        ]);

        Sanctum::actingAs($user);

        $bookingId = $this->postJson('/api/bookings', [
            'flight_id' => $flight->id,
            'seat_count' => 2,
        ])->assertCreated()
            ->assertJsonPath('success', true)
            ->json('data.id');

        $this->assertDatabaseHas('flights', [
            'id' => $flight->id,
            'available_seats' => 8,
        ]);

        $this->getJson('/api/bookings')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->postJson("/api/bookings/{$bookingId}/passengers", [
            'first_name' => 'Jad',
            'last_name' => 'Ozeir',
            'passport_number' => 'A123456',
            'nationality' => 'Lebanese',
            'date_of_birth' => '1996-05-13',
        ])->assertCreated();

        $this->postJson("/api/bookings/{$bookingId}/passengers", [
            'first_name' => 'Elissar',
            'last_name' => 'Ozeir',
            'passport_number' => 'B123456',
            'nationality' => 'Lebanese',
            'date_of_birth' => '1998-08-09',
        ])->assertCreated();

        $this->postJson("/api/bookings/{$bookingId}/passengers", [
            'first_name' => 'Extra',
            'last_name' => 'Passenger',
        ])->assertUnprocessable();

        $this->postJson("/api/flights/{$flight->id}/save")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->getJson('/api/saved-flights')
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->deleteJson("/api/flights/{$flight->id}/unsave")
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->patchJson("/api/bookings/{$bookingId}/cancel")
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');

        $this->assertDatabaseHas('flights', [
            'id' => $flight->id,
            'available_seats' => 10,
        ]);
    }

    public function test_admin_can_view_all_bookings_but_regular_user_cannot(): void
    {
        $user = $this->createUser('user');
        $flight = $this->createFlight();

        Sanctum::actingAs($user);

        $this->postJson('/api/bookings', [
            'flight_id' => $flight->id,
            'seat_count' => 1,
        ])->assertCreated();

        $this->getJson('/api/admin/bookings')
            ->assertForbidden()
            ->assertJsonPath('success', false);

        Sanctum::actingAs($this->createUser('admin'));

        $this->getJson('/api/admin/bookings')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 1);
    }

    private function createUser(string $role): User
    {
        return User::factory()->create([
            'role' => $role,
        ]);
    }

    private function createFlight(array $overrides = []): Flight
    {
        $airline = Airline::create([
            'name' => 'BabiVoyage Air',
            'code' => fake()->unique()->lexify('??'),
        ]);

        $originAirport = Airport::create([
            'name' => 'Origin International Airport',
            'code' => fake()->unique()->lexify('???'),
            'city' => 'Abidjan',
            'country' => "Cote d'Ivoire",
        ]);

        $destinationAirport = Airport::create([
            'name' => 'Destination International Airport',
            'code' => fake()->unique()->lexify('???'),
            'city' => 'Accra',
            'country' => 'Ghana',
        ]);

        return Flight::create(array_merge([
            'flight_number' => fake()->unique()->bothify('BV-###'),
            'airline_id' => $airline->id,
            'origin_airport_id' => $originAirport->id,
            'destination_airport_id' => $destinationAirport->id,
            'departure_time' => now()->addDays(7),
            'arrival_time' => now()->addDays(7)->addHours(2),
            'price' => 145.00,
            'total_seats' => 100,
            'available_seats' => 100,
            'status' => 'scheduled',
        ], $overrides));
    }
}
