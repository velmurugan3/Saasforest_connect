<?php

namespace App\Filament\Resources\DailyWorkResource\Pages;

use App\Filament\Resources\DailyWorkResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDailyWork extends EditRecord
{
    protected static string $resource = DailyWorkResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
