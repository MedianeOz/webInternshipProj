<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FlightResource\Pages;
use App\Models\Flight;
use Closure;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class FlightResource extends Resource
{
    protected static ?string $model = Flight::class;

    protected static ?string $navigationIcon = 'heroicon-o-paper-airplane';

    protected static ?string $navigationGroup = 'Operations';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('flight_number')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(20),
                Forms\Components\Select::make('airline_id')
                    ->required()
                    ->relationship('airline', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('origin_airport_id')
                    ->label('Origin Airport')
                    ->required()
                    ->relationship('originAirport', 'name')
                    ->searchable()
                    ->preload(),
                Forms\Components\Select::make('destination_airport_id')
                    ->label('Destination Airport')
                    ->required()
                    ->relationship('destinationAirport', 'name')
                    ->searchable()
                    ->preload()
                    ->different('origin_airport_id'),
                Forms\Components\DateTimePicker::make('departure_time')
                    ->required(),
                Forms\Components\DateTimePicker::make('arrival_time')
                    ->required()
                    ->rule('after_or_equal:departure_time'),
                Forms\Components\TextInput::make('price')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->prefix('$'),
                Forms\Components\TextInput::make('total_seats')
                    ->numeric()
                    ->required()
                    ->minValue(1)
                    ->rules(['integer']),
                Forms\Components\TextInput::make('available_seats')
                    ->numeric()
                    ->required()
                    ->minValue(0)
                    ->rules([
                        'integer',
                        fn (Forms\Get $get): Closure => function (string $attribute, mixed $value, Closure $fail) use ($get): void {
                            $totalSeats = $get('total_seats');

                            if (filled($value) && filled($totalSeats) && (int) $value > (int) $totalSeats) {
                                $fail('The available seats field must be less than or equal to total seats.');
                            }
                        },
                    ]),
                Forms\Components\Select::make('status')
                    ->required()
                    ->options([
                        'scheduled' => 'Scheduled',
                        'delayed' => 'Delayed',
                        'cancelled' => 'Cancelled',
                        'completed' => 'Completed',
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->sortable(),
                Tables\Columns\TextColumn::make('flight_number')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('airline.name')
                    ->label('Airline')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('originAirport.code')
                    ->label('From')
                    ->searchable(),
                Tables\Columns\TextColumn::make('destinationAirport.code')
                    ->label('To')
                    ->searchable(),
                Tables\Columns\TextColumn::make('departure_time')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('arrival_time')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                Tables\Columns\TextColumn::make('price')
                    ->prefix('$')
                    ->sortable(),
                Tables\Columns\TextColumn::make('available_seats')
                    ->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'scheduled' => 'primary',
                        'delayed' => 'warning',
                        'cancelled' => 'danger',
                        'completed' => 'success',
                    })
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'scheduled' => 'Scheduled',
                        'delayed' => 'Delayed',
                        'cancelled' => 'Cancelled',
                        'completed' => 'Completed',
                    ]),
                Tables\Filters\SelectFilter::make('airline_id')
                    ->label('Airline')
                    ->relationship('airline', 'name'),
            ])
            ->defaultSort('departure_time', 'desc')
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListFlights::route('/'),
            'create' => Pages\CreateFlight::route('/create'),
            'edit' => Pages\EditFlight::route('/{record}/edit'),
        ];
    }
}
