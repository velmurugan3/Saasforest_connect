<?php

namespace App\Filament\Resources\Settings\AllowanceResource\Pages;

use App\Filament\Resources\Settings\AllowanceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAllowance extends EditRecord
{
    protected static string $resource = AllowanceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
