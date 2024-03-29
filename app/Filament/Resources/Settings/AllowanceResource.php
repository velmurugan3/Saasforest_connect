<?php

namespace App\Filament\Resources\Settings;

use App\Filament\Resources\Settings\AllowanceResource\Pages;
use App\Filament\Resources\Settings\AllowanceResource\RelationManagers;
use App\Models\Payroll\Allowance;
use Filament\Forms;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AllowanceResource extends Resource
{
    protected static ?string $model = Allowance::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Card::make([
                    TextInput::make('name')
                    ->maxLength(255)

                    ->required()
                    ->label('Allowance Name'),
                    Select::make('company_id')
                    ->relationship('company', 'name')
                    ->required(),
                    Select::make('frequency')
                    ->options([
                        'daily' => 'daily',
                        'weekly' => 'weekly',
                         'monthly' => 'monthly'
                        ])
                    ->required(),
                    TextInput::make('max_count')
                    ->numeric()
                    ->required(),
                    TextInput::make('amount')
                    ->numeric()
                    ->default(0),
                    TextInput::make('percentage')
                    ->numeric()
                    ->default(0)
                        ->suffixIcon('antdesign-percentage-o'),
                        Toggle::make('is_fixed')
                    ->label('Fixed')

                        ->default(true),
                        Toggle::make('before_tax')
                        ->default(true),


                    ])
                ->columns(2)

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name'),
                TextColumn::make('company.name'),
                TextColumn::make('frequency'),
                TextColumn::make('max_count'),
                ToggleColumn::make('is_fixed')
                ->label('Fixed')
                ,
                TextColumn::make('amount'),


                ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateActions([
                // Tables\Actions\CreateAction::make()

            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListAllowances::route('/'),
            'create' => Pages\CreateAllowance::route('/create'),
            'edit' => Pages\EditAllowance::route('/{record}/edit'),
        ];
    }
}
