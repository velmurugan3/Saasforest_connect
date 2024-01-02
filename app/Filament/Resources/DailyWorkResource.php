<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DailyWorkResource\Pages;
use App\Filament\Resources\DailyWorkResource\RelationManagers;
use App\Models\DailyWork;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class DailyWorkResource extends Resource
{
    protected static ?string $model = DailyWork::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;

    // protected static ?string $navigationGroup = 'Daily';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                //
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
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
            'index' => Pages\Work::route('/'),
            // 'create' => Pages\CreateDailyWork::route('/create'),
            // 'create' => pages\ThemeColor::route('/{record}/create'),
            'edit' => Pages\EditDailyWork::route('/{record}/create'),
        ];
    }
}
