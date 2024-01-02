<?php

namespace App\Filament\Resources\Finance\ReportResource\Pages;

use App\Filament\Resources\Finance\ReportResource;
use App\Filament\Resources\Finance\ReportResource\Widgets\BudgetOverview;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListReports extends ListRecords
{
    protected static string $resource = ReportResource::class;

    protected function getHeaderWidgets(): array    {
        return [
           BudgetOverview::class,
        ];
    }
    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
