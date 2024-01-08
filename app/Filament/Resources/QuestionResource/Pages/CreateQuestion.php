<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use App\Models\TaskUser;
use App\Models\User;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateQuestion extends CreateRecord
{
    protected static string $resource = QuestionResource::class;

    protected function afterCreate(): void
    {
        $a = $this->data;

        Notification::make()
        ->title('New Notification Assigned')
        ->body('New Task Created')
        ->actions([
            Action::make('View')->url(
                QuestionResource::getUrl('edit',['record' => $a])
            )
        ])
        ->sendToDatabase(auth()->user());

        // dd($this->record->id);
        // dd($a);

        foreach ($a['user_id'] as $b) {
            TaskUser::create([
                'user_id' => $b,
                'question_id' => $this->record->id,
            ]);
        }                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                   
    }
    
}
