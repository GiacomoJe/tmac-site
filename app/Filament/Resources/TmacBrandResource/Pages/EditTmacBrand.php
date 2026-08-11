<?php

namespace App\Filament\Resources\TmacBrandResource\Pages;

use App\Filament\Resources\TmacBrandResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTmacBrand extends EditRecord
{
    protected static string $resource = TmacBrandResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
