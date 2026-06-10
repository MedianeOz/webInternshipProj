<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BookingResource\Pages;
use App\Filament\Resources\BookingResource\RelationManagers\PassengersRelationManager;
use App\Models\Booking;
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
            'view' => Pages\ViewBooking::route('/{record}'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit($record): bool
    {
        return false;
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
