<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QuestionResource\Pages;
use App\Filament\Resources\QuestionResource\RelationManagers;
use App\Models\Question;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Symfony\Component\Console\Input\Input;

class QuestionResource extends Resource
{
    protected static ?string $model = Question::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        TextInput::make('title')
                            ->label('What question do you want to ask?'),
                        Radio::make('status')
                            ->label('How often do you want to ask?')
                            ->options([
                                'Daily On' => 'Daily On',
                                'Once a Week' => 'Once a Week',
                                'Every other week' => 'Every other week',
                                'Once a month on the first' => 'Once a month on the first'
                            ]),
                        // Radio::make('time')
                        //     ->label('At what time of the day?')
                        //     ->options([
                        //         'Beginning of the day (10:00 AM )' => 'Beginning of the day (10:00 AM )',
                        //         'End of the day (06:00 PM )' => 'End of the day (06:00 PM )',
                        //         'Let me pick a time' => 'Let me pick a time',
                        //     ]),
                        TimePicker::make('time')
                        ->label('At what time of the day?'),
                        Select::make('user_id')
                            ->label('Who do you want to ask?')
                            ->multiple()
                            ->options(User::all()->pluck('name', 'id'))
                            ->searchable()
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title'),
                TextColumn::make('status'),
                TextColumn::make('time'),
                TextColumn::make('userName.id'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
               
                        
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                // Tables\Actions\CreateAction::make()->label('New Timesheet')
                // ->after(function(){
                //     dd('yes');
                // })
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
            'index' => Pages\ListQuestions::route('/'),
            'create' => Pages\CreateQuestion::route('/create'),
            'edit' => Pages\AddQuestion::route('/{record}/edit'),
            'question' => Pages\AddQuestion::route('/question'),
        ];
    }
}
