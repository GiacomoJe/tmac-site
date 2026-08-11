<?php

namespace App\Filament\Resources\TmacBrandResource\Pages;

use App\Filament\Resources\TmacBrandResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTmacBrands extends ListRecords
{
    protected static string $resource = TmacBrandResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
