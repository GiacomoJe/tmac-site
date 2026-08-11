<?php

namespace App\Filament\Resources\MotorcycleModelResource\Pages;

use App\Filament\Resources\MotorcycleModelResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMotorcycleModels extends ListRecords
{
    protected static string $resource = MotorcycleModelResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
