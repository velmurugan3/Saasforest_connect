<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use App\Models\Question;
use App\Models\User;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;

class AddQuestion extends Page
{
    protected static string $resource = QuestionResource::class;

    protected static string $view = 'filament.resources.question-resource.pages.add-question';

    public $dailys;
    public $times;
    public $users;
    public $description;
    public $status;
    public $day;
    public $day1;
    public $userName;

    public function createQuestion(){
        // dd('Velu');
        dd($this->userName);

        if ($this->day == 'Beginning of the day (10:00 AM )') {
            $this->day = '10:00';
            $question = Question::create([
                'title' => $this->description,
                'status' => $this->status,
                'time' => $this->day,
            ]);
        }
        elseif($this->day == 'End of the day (06:00 PM )'){
            $this->day = '18:00';
            $question = Question::create([
                'title' => $this->description,
                'status' => $this->status,
                'time' => $this->day,
            ]);
        }
        elseif($this->day == ''){
            $question = Question::create([
                'title' => $this->description,
                'status' => $this->status,
                'time' => $this->day,
            ]);
        }
       
        // $question = Question::create([
        //     'title' => $this->description,
        //     'status' => $this->status,
        //     'time' => $this->day,
        // ]);

        Notification::make()
        ->title('Saved successfully')
        ->success()
        ->send();
    }

    public function mount(): void
    {

        $this->users = User::all();
        // dd($this->users);
        $this->dailys = ['Daily On', 'Once a Week', 'Every other week', 'Once a month on the first'];
        $this->times = ['Beginning of the day (10:00 AM )','End of the day (06:00 PM )'];
        // dd('Velu');
        static::authorizeResourceAccess();
    }
}
