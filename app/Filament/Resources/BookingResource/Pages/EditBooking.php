<?php

namespace App\Filament\Resources\BookingResource\Pages;

use App\Filament\Resources\BookingResource;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditBooking extends EditRecord
{
    protected static string $resource = BookingResource::class;

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        if ($record->status !== 'pending') {
            throw ValidationException::withMessages([
                'data.status' => 'Only pending bookings can be edited.',
            ]);
        }

        if ($data['status'] !== 'confirmed') {
            throw ValidationException::withMessages([
                'data.status' => 'Pending bookings can only be changed to confirmed.',
            ]);
        }

        $record->update([
            'status' => 'confirmed',
        ]);

        return $record;
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', [
            'record' => $this->record,
        ]);
    }
}
