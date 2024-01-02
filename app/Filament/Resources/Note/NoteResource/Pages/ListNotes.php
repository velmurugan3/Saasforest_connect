<?php

namespace App\Filament\Resources\Note\NoteResource\Pages;

use App\Filament\Resources\Note\NoteResource;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListNotes extends ListRecords
{
    protected static string $resource = NoteResource::class;

    protected function getActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
