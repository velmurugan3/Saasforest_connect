<?php

namespace App\Filament\Resources\DateResource\Pages;

use App\Filament\Resources\DateResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditDate extends EditRecord
{
    protected static string $resource = DateResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
