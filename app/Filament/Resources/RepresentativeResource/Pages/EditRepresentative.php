<?php

namespace App\Filament\Resources\RepresentativeResource\Pages;

use App\Filament\Resources\RepresentativeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRepresentative extends EditRecord
{
    protected static string $resource = RepresentativeResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
