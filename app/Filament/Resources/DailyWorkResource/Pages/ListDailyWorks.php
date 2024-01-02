<?php

namespace App\Filament\Resources\DailyWorkResource\Pages;

use App\Filament\Resources\DailyWorkResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListDailyWorks extends ListRecords
{
    protected static string $resource = DailyWorkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
