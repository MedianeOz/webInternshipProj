# BabiVoyage

BabiVoyage is a Laravel 12 RESTful API backend for a West African flight booking platform. It includes Sanctum API authentication, flight search, bookings, passengers, saved flights, profile management, admin API routes, automated tests, a Postman collection, and a Filament v3 admin panel bonus task.

## Tech Stack

- Laravel 12
- PHP 8.2+ (PHP 8.4 recommended for this WAMP setup)
- MySQL
- Laravel Sanctum
- Filament v3
- PHPUnit
- Postman

## Main Features

- Register, login, and logout with Sanctum tokens
- Public flight listing and search
- Admin-only flight create, update, and delete API routes
- User bookings with automatic seat reduction
- Booking cancellation with seat release
- Passenger management per booking
- Saved flights wishlist
- Profile view and update
- Admin route to view all bookings
- Standard JSON API error responses
- Paginated flight and admin booking results
- Filament admin panel at `/admin`
- Dashboard stats for flights, bookings, users, and revenue

## Completed Tasks

### 1. Laravel Project Setup

The project was created in:

```text
C:\wamp64\www\webInternshipProj
```

The local MySQL database is:

```text
webInternship_db
```

Required `.env` database settings:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=webInternship_db
DB_USERNAME=root
DB_PASSWORD=
```

### 2. Database And Migrations

Main tables:

- `users`
- `airlines`
- `airports`
- `flights`
- `bookings`
- `passengers`
- `saved_flights`
- `personal_access_tokens`

The `users` table was extended with:

- `phone`
- `role` (`admin` or `user`)

### 3. Models And Relationships

Main models:

- `User`
- `Airline`
- `Airport`
- `Flight`
- `Booking`
- `Passenger`

Key relationships:

- User has many bookings
- User belongs to many saved flights
- Airline has many flights
- Airport has many flights as origin and destination
- Flight belongs to airline, origin airport, and destination airport
- Flight has many bookings
- Booking belongs to user and flight
- Booking has many passengers
- Passenger belongs to booking

### 4. Authentication API

Authentication uses Laravel Sanctum.

Files:

- `app/Http/Controllers/API/AuthController.php`
- `app/Http/Requests/Auth/RegisterRequest.php`
- `app/Http/Requests/Auth/LoginRequest.php`

Endpoints:

| Method | Endpoint | Description |
| --- | --- | --- |
| POST | `/api/register` | Register a user |
| POST | `/api/login` | Login and get token |
| POST | `/api/logout` | Logout and revoke tokens |

Register and login are protected with:

```text
throttle:10,1
```

### 5. Flights API

Files:

- `app/Http/Controllers/API/FlightController.php`
- `app/Http/Requests/Flight/StoreFlightRequest.php`
- `app/Http/Requests/Flight/UpdateFlightRequest.php`

Public endpoints:

| Method | Endpoint | Description |
| --- | --- | --- |
| GET | `/api/flights` | List scheduled flights |
| GET | `/api/flights/search` | Search flights |
| GET | `/api/flights/{id}` | Show one flight |

Admin endpoints:

| Method | Endpoint | Description |
| --- | --- | --- |
| POST | `/api/admin/flights` | Create flight |
| PUT | `/api/admin/flights/{id}` | Update flight |
| DELETE | `/api/admin/flights/{id}` | Delete flight |

Search supports origin, destination, date, minimum price, and maximum price.

### 6. Bookings API

Files:

- `app/Http/Controllers/API/BookingController.php`
- `app/Http/Requests/Booking/StoreBookingRequest.php`

Endpoints:

| Method | Endpoint | Description |
| --- | --- | --- |
| GET | `/api/bookings` | List user's bookings |
| POST | `/api/bookings` | Create booking |
| GET | `/api/bookings/{id}` | Show owned booking |
| PATCH | `/api/bookings/{id}/cancel` | Cancel owned booking |
| GET | `/api/admin/bookings` | Admin list of all bookings |

Booking logic:

- checks flight availability
- checks available seats
- creates a `BV-` booking reference
- saves confirmed bookings
- decreases seats after booking
- releases seats after cancellation

### 7. Passengers API

Files:

- `app/Http/Controllers/API/PassengerController.php`
- `app/Http/Requests/Passenger/StorePassengerRequest.php`

Endpoints:

| Method | Endpoint | Description |
| --- | --- | --- |
| GET | `/api/bookings/{id}/passengers` | List passengers |
| POST | `/api/bookings/{id}/passengers` | Add passenger |
| DELETE | `/api/bookings/{id}/passengers/{pid}` | Remove passenger |

Rules:

- only booking owner can manage passengers
- cannot add passengers to cancelled booking
- passenger count cannot exceed booked seats

### 8. Saved Flights API

File:

```text
app/Http/Controllers/API/SavedFlightController.php
```

Endpoints:

| Method | Endpoint | Description |
| --- | --- | --- |
| GET | `/api/saved-flights` | List saved flights |
| POST | `/api/flights/{id}/save` | Save flight |
| DELETE | `/api/flights/{id}/unsave` | Remove saved flight |

This uses the `saved_flights` pivot table.

### 9. Profile API

Files:

- `app/Http/Controllers/API/ProfileController.php`
- `app/Http/Requests/Profile/UpdateProfileRequest.php`

Endpoints:

| Method | Endpoint | Description |
| --- | --- | --- |
| GET | `/api/profile` | Show profile |
| PUT | `/api/profile` | Update profile |

Allowed update fields:

- name
- phone
- password

### 10. JSON Error Handling

The API returns consistent JSON for:

- validation errors
- unauthenticated requests
- unauthorized requests
- unexpected API errors

Example validation response:

```json
{
    "success": false,
    "message": "Validation failed.",
    "errors": {}
}
```

### 11. Postman And Tests

Postman collection:

```text
postman/BabiVoyage Local.postman_collection.json
```

Main test file:

```text
tests/Feature/BabiVoyageRequirementsTest.php
```

Current test result:

```text
Tests: 9 passed (62 assertions)
```

## Bonus Task: Filament Admin Panel

The project includes a Filament v3 admin panel.

URL:

```text
http://127.0.0.1:8000/admin
```

Package:

```json
"filament/filament": "^3.0"
```

Admin login created by `AdminSeeder`:

```text
Email: admin@babivoyage.com
Password: admin123456
```

### Filament Access Control

The `User` model implements Filament access with:

```php
public function canAccessPanel(Panel $panel): bool
{
    return $this->role === 'admin';
}
```

Only users with `role = admin` can enter the panel.

### Filament Files

Provider:

```text
app/Providers/Filament/AdminPanelProvider.php
```

Resources:

```text
app/Filament/Resources/AirlineResource.php
app/Filament/Resources/AirportResource.php
app/Filament/Resources/FlightResource.php
app/Filament/Resources/BookingResource.php
app/Filament/Resources/UserResource.php
```

Dashboard widget:

```text
app/Filament/Widgets/StatsOverviewWidget.php
```

### Filament Admin Features

- Dashboard stats
- Flight create/edit/list
- Airline create/edit/list
- Airport create/edit/list
- User create/edit/list
- Read-only bookings
- Booking details page
- Read-only passengers relation table
- Cancel booking action with seat release

The booking resource is read-only because bookings are created through the API.

## API Routes Summary

### Public

| Method | Endpoint |
| --- | --- |
| POST | `/api/register` |
| POST | `/api/login` |
| GET | `/api/flights` |
| GET | `/api/flights/search` |
| GET | `/api/flights/{id}` |

### Authenticated User

| Method | Endpoint |
| --- | --- |
| POST | `/api/logout` |
| GET | `/api/bookings` |
| POST | `/api/bookings` |
| GET | `/api/bookings/{id}` |
| PATCH | `/api/bookings/{id}/cancel` |
| GET | `/api/bookings/{id}/passengers` |
| POST | `/api/bookings/{id}/passengers` |
| DELETE | `/api/bookings/{id}/passengers/{pid}` |
| GET | `/api/saved-flights` |
| POST | `/api/flights/{id}/save` |
| DELETE | `/api/flights/{id}/unsave` |
| GET | `/api/profile` |
| PUT | `/api/profile` |

### Admin API

| Method | Endpoint |
| --- | --- |
| POST | `/api/admin/flights` |
| PUT | `/api/admin/flights/{id}` |
| DELETE | `/api/admin/flights/{id}` |
| GET | `/api/admin/bookings` |

## Local Setup

Start WAMP first and make sure MySQL is running.

Then:

```powershell
cd C:\wamp64\www\webInternshipProj
composer install
copy .env.example .env
C:\wamp64\bin\php\php8.4.0\php.exe artisan key:generate
C:\wamp64\bin\php\php8.4.0\php.exe artisan migrate
C:\wamp64\bin\php\php8.4.0\php.exe artisan db:seed
C:\wamp64\bin\php\php8.4.0\php.exe artisan optimize:clear
C:\wamp64\bin\php\php8.4.0\php.exe artisan serve
```

Open:

```text
http://127.0.0.1:8000
http://127.0.0.1:8000/admin
```

## Useful Commands

Run tests:

```powershell
C:\wamp64\bin\php\php8.4.0\php.exe artisan test
```

Show API routes:

```powershell
C:\wamp64\bin\php\php8.4.0\php.exe artisan route:list --path=api --except-vendor
```

Show admin routes:

```powershell
C:\wamp64\bin\php\php8.4.0\php.exe artisan route:list --path=admin
```

Seed only the admin user:

```powershell
C:\wamp64\bin\php\php8.4.0\php.exe artisan db:seed --class=AdminSeeder
```

## Notes

- API requests use Sanctum bearer tokens.
- Filament uses browser session login with the `web` guard.
- Use PHP 8.4 from WAMP for local Artisan commands.
- If `/admin` gives a database connection error, check that MySQL is running.

