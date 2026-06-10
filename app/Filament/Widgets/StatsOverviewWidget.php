<?php

namespace App\Filament\Widgets;

use App\Models\Booking;
use App\Models\Flight;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseStatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseStatsOverviewWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Flights', Flight::count())
                ->color('blue'),
            Stat::make('Total Bookings', Booking::count())
                ->color('primary'),
            Stat::make('Confirmed Bookings', Booking::where('status', 'confirmed')->count())
                ->color('success'),
            Stat::make('Cancelled Bookings', Booking::where('status', 'cancelled')->count())
                ->color('danger'),
            Stat::make('Registered Users', User::where('role', 'user')->count())
                ->color('warning'),
            Stat::make('Total Revenue', '$'.number_format(Booking::where('status', 'confirmed')->sum('total_price'), 2))
                ->color('success'),
        ];
    }
}
