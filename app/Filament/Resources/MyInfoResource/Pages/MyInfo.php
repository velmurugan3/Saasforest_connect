<?php

namespace App\Filament\Resources\MyInfoResource\Pages;

use App\Filament\Resources\MyInfoResource;
use App\Models\Employee\CurrentAddress;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Resources\Pages\Page;

class MyInfo extends Page implements HasForms
{
    use InteractsWithForms;

    public ?array $data = [];
    public ?array $address = [];

    protected static string $resource = MyInfoResource::class;

    protected static string $view = 'filament.resources.my-info-resource.pages.my-info';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('Name')
                    ->required(),
                TextInput::make('Date of birth')
                    ->required(),
                TextInput::make('Employee ID')
                    ->required(),
                TextInput::make('Department')
                    ->required(),
                TextInput::make('Employee type')
                    ->required(),
                TextInput::make('Email')
                    ->required(),

            ])
            ->columns(2)
            ->statePath('data');
    }

    public function addressForm(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('Country')
                    ->required(),
                TextInput::make('Street address')
                    ->required(),
                TextInput::make('City')
                    ->required(),
                TextInput::make('State / Province')
                    ->required(),
                TextInput::make('Zip / Postal code')
                    ->required(),
            ])
            ->columns(2)
            ->statePath('address')
            ->model(CurrentAddress::class);
    }

    protected function getForms(): array
    {
        return [
            'form',
            'addressForm',
        ];
    }

    public function mount(): void
    {
        $this->form->fill();

        $this->addressForm->fill();

        static::authorizeResourceAccess();
    }
}
