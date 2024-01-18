<?php

namespace App\Filament\Resources\Employee\EmployeeResource\Pages;

use App\Filament\Resources\Employee\EmployeeResource;
use App\Models\Employee\Employee;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;

class AddEmployee extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = EmployeeResource::class;

    protected static string $view = 'filament.resources.employee.employee-resource.pages.add-employee';

    public ?array $work = [];

    // Action::make('updateAuthor')
    // ->form([
    //     // ...
    // ])
    // ->action(function (array $data): void {
    //     // ...
    // })
    // ->slideOver()

    public function workForm(Form $form): Form
    {

        return $form
            ->schema([
                TextInput::make('content')
                    ->required(),
            ])
            ->statePath('work')
            ->model(Employee::class);
    }

    protected function getForms(): array
    {
        return [
            'workForm',
        ];
    }

    public function mount(): void
    {
        $this->workForm->fill();

        static::authorizeResourceAccess();
    }
}
