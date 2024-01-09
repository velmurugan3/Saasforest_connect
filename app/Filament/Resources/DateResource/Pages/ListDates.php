<?php

namespace App\Filament\Resources\DateResource\Pages;

use App\Filament\Resources\DateResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDates extends ListRecords
{
    protected static string $resource = DateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
