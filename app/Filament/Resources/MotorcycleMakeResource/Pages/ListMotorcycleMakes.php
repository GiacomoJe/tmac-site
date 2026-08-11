<?php

namespace App\Filament\Resources\MotorcycleMakeResource\Pages;

use App\Filament\Resources\MotorcycleMakeResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMotorcycleMakes extends ListRecords
{
    protected static string $resource = MotorcycleMakeResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
