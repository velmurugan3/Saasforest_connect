<?php

namespace App\Filament\Resources\Employee\EmployeeResource\Pages;

use App\Filament\Resources\Employee\EmployeeResource;
use App\Filament\Resources\Employee\EmployeeResource\Pages\AddEmployee;
use EightyNine\ExcelImport\ExcelImportAction;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Grid;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Infolists\Infolist;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;
use Konnco\FilamentImport\Actions\ImportAction;
use Konnco\FilamentImport\Actions\ImportField;

class ListEmployees extends ListRecords
{

    protected static string $resource = EmployeeResource::class;

    protected static ?string $title = 'Employees';

    protected function getActions(): array
    {
        return [

            Action::make('Add Employee')
                ->form([
                    Grid::make(2)
                        ->schema([
                            TextInput::make('First Name')
                            ->required(),
                            TextInput::make('Last Name')
                            ->required(),
                            Select::make('Gender')
                            ->required(),
                            TextInput::make('Employee ID')
                            ->required(),
                            TextInput::make('Email Address')
                            ->required(),
                            Select::make('Role')
                            ->required(),
                            DatePicker::make('Joining Date')
                            ->required(),
                            Select::make('Employee Type')
                            ->required(),
                            Select::make('Reporting To')
                            ->required(),
                            Select::make('Payroll Policy')
                            ->required(),
                            Select::make('Time Off  Policy')
                            ->required(),
                        ])
                ])
                ->slideOver(),

            // Action::make('Add Employee')
            //     ->url(fn (): string => route('filament.admin.resources.employees.add'))
            //     ->slideOver(),

            // Action::make('Add Employee'),

            Actions\CreateAction::make()->label('New Employee'),
            //    ExcelImportAction::make()
            //    ->use(\App\Imports\EmployeeImport::class)
            //     ->color("primary")
            //    ,

            
            Action::make('import')
                ->hidden(!auth()->user()->hasPermissionTo('Employee Profiles'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('primary')
                ->url(fn (): string => route('filament.admin.resources.employees.import')),
                
        ];
    }


    protected function getTableContentGrid(): ?array
    {
        return [
            'md' => 2,
            'xl' => 3,
        ];
    }
}
