<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Filament\Resources\BookingResource\RelationManagers\PassengersRelationManager;
use App\Models\Booking;
use App\Models\Flight;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;

class BookingResource extends Resource
{
    protected static ?string $model = Booking::class;

    protected static ?string $navigationIcon = 'heroicon-o-ticket';

    protected static ?string $navigationGroup = 'Operations';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('user_id')
                    ->label('Customer')
                    ->required()
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->relationship('user', 'name')
                    ->getOptionLabelFromRecordUsing(fn ($record): string => "{$record->name} ({$record->email})")
                    ->searchable(['name', 'email'])
                    ->preload(),
                Forms\Components\Select::make('flight_id')
                    ->label('Flight')
                    ->required()
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->relationship(
                        name: 'flight',
                        titleAttribute: 'flight_number',
                        modifyQueryUsing: fn ($query) => $query
                            ->where('status', 'scheduled')
                            ->where('available_seats', '>', 0)
                            ->orderBy('departure_time'),
                    )
                    ->getOptionLabelFromRecordUsing(
                        fn (Flight $record): string => "{$record->flight_number} - {$record->originAirport?->code} to {$record->destinationAirport?->code} ({$record->available_seats} seats left)"
                    )
                    ->searchable()
                    ->preload()
                    ->live(),
                Forms\Components\TextInput::make('seat_count')
                    ->required()
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(10)
                    ->live()
                    ->rules([
                        'integer',
                        fn (Forms\Get $get): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                            $flightId = $get('flight_id');

                            if (! filled($flightId) || ! filled($value)) {
                                return;
                            }

                            $flight = Flight::find($flightId);

                            if (! $flight) {
                                $fail('The selected flight does not exist.');

                                return;
                            }

                            if ($flight->status !== 'scheduled') {
                                $fail('This flight is not available for booking.');

                                return;
                            }

                            if ((int) $value > $flight->available_seats) {
                                $fail('Not enough seats available. Only '.$flight->available_seats.' seats left.');
                            }
                        },
                    ]),
                Forms\Components\Placeholder::make('estimated_total')
                    ->label('Estimated Total')
                    ->visible(fn (string $operation): bool => $operation === 'create')
                    ->content(function (Forms\Get $get): string {
                        $flightId = $get('flight_id');
                        $seatCount = (int) $get('seat_count');

                        if (! filled($flightId) || $seatCount < 1) {
                            return '$0.00';
                        }

                        $flight = Flight::find($flightId);

                        if (! $flight) {
                            return '$0.00';
                        }

                        return '$'.number_format((float) $flight->price * $seatCount, 2);
                    }),
                Forms\Components\Select::make('status')
                    ->required()
                    ->options(fn (string $operation): array => $operation === 'edit'
                        ? ['confirmed' => 'Confirmed']
                        : [
                            'pending' => 'Pending',
                            'confirmed' => 'Confirmed',
                        ])
                    ->default('confirmed')
                    ->rules(fn (string $operation): array => [
                        $operation === 'edit' ? 'in:confirmed' : 'in:pending,confirmed',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('booking_reference')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Passenger Name')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('user.email')
                    ->label('Email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('flight.flight_number')
                    ->label('Flight')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('seat_count')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_price')
                    ->prefix('$')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'cancelled' => 'danger',
                    })
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Booked At')
                    ->date()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'pending' => 'Pending',
                        'confirmed' => 'Confirmed',
                        'cancelled' => 'Cancelled',
                    ]),
                Tables\Filters\SelectFilter::make('flight_id')
                    ->label('Flight')
                    ->relationship('flight', 'flight_number'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn (Booking $record): bool => $record->status === 'pending'),
                Tables\Actions\Action::make('cancel')
                    ->label('Cancel')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Are you sure you want to cancel this booking?')
                    ->visible(fn (Booking $record): bool => $record->status !== 'cancelled')
                    ->action(function (Booking $record): void {
                        DB::transaction(function () use ($record): void {
                            $record->load('flight');
                            $record->flight?->increment('available_seats', $record->seat_count);
                            $record->update(['status' => 'cancelled']);
                        });

                        Notification::make()
                            ->title('Booking cancelled successfully.')
                            ->success()
                            ->send();
                    }),
            ]);
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Infolists\Components\TextEntry::make('id'),
                Infolists\Components\TextEntry::make('user.name')
                    ->label('Passenger Name'),
                Infolists\Components\TextEntry::make('user.email')
                    ->label('Email'),
                Infolists\Components\TextEntry::make('flight.flight_number')
                    ->label('Flight'),
                Infolists\Components\TextEntry::make('booking_reference'),
                Infolists\Components\TextEntry::make('seat_count'),
                Infolists\Components\TextEntry::make('total_price')
                    ->prefix('$'),
                Infolists\Components\TextEntry::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'pending' => 'warning',
                        'confirmed' => 'success',
                        'cancelled' => 'danger',
                    }),
                Infolists\Components\TextEntry::make('created_at')
                    ->dateTime('d M Y H:i'),
                Infolists\Components\TextEntry::make('updated_at')
                    ->dateTime('d M Y H:i'),
            ])
            ->columns(2);
    }

    public static function getRelations(): array
    {
        return [
            PassengersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListBookings::route('/'),
            'create' => Pages\CreateBooking::route('/create'),
            'edit' => Pages\EditBooking::route('/{record}/edit'),
            'view' => Pages\ViewBooking::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function canEdit($record): bool
    {
        return $record->status === 'pending';
    }

    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
