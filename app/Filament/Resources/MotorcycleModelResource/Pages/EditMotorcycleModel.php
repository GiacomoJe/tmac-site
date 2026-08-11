<?php

namespace App\Filament\Resources\MotorcycleModelResource\Pages;

use App\Filament\Resources\MotorcycleModelResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMotorcycleModel extends EditRecord
{
    protected static string $resource = MotorcycleModelResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
