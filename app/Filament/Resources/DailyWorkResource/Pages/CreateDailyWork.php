<?php

namespace App\Filament\Resources\DailyWorkResource\Pages;

use App\Filament\Resources\DailyWorkResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateDailyWork extends CreateRecord
{
    protected static string $resource = DailyWorkResource::class;
}
