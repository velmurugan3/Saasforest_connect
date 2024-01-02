<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use App\Models\TaskUser;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateQuestion extends CreateRecord
{
    protected static string $resource = QuestionResource::class;

    protected function afterCreate(): void
    {
        $a = $this->data;
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
