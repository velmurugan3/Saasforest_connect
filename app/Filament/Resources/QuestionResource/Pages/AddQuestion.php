<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use App\Models\Question;
use App\Models\TaskUser;
use App\Models\User;
use DateTime;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;
use Livewire\Attributes\Validate;

class AddQuestion extends Page implements HasForms
{
    use InteractsWithForms;

    protected static string $resource = QuestionResource::class;

    protected static string $view = 'filament.resources.question-resource.pages.add-question';

    public $dailys;
    public $times;
    public $users;
    #[Validate('required|min:3')]
    public $description;
    public $record;
    public $status;
    public $day;
    public $day1;
    public $userName;
    public ?array $data = [];
    public $a;
    public $date;
    public $updateVal;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('user_id')
                    ->label('Who do you want to ask?')
                    ->multiple()
                    ->options(User::all()->pluck('name', 'id'))
                    ->searchable(),
            ])
            ->statePath('data');
        // ->model(TaskUser::class);
    }

    protected function getForms(): array
    {
        return [
            'form',
        ];
    }

    public function createQuestion(): void
    {
        // dd($this->form->getState());
        // dd($this->day1);

        if ($this->day == 'Beginning of the day (10:00 AM )') {
            $this->day = '10:00';
            $question = Question::create([
                'title' => $this->description,
                'status' => $this->status,
                'time' => $this->day,
            ]);
        } elseif ($this->day == 'End of the day (06:00 PM )') {
            $this->day = '18:00';
            $question = Question::create([
                'title' => $this->description,
                'status' => $this->status,
                'time' => $this->day,
            ]);
        } elseif ($this->day == '') {
            $question = Question::create([
                'title' => $this->description,
                'status' => $this->status,
                'time' => $this->day1,
            ]);
        }

        $userData = $this->form->getState();

        foreach ($userData['user_id'] as $b) {
            TaskUser::create([
                'user_id' => $b,
                'question_id' => $question->id,
            ]);
        }
        Notification::make()
            ->title('Saved successfully')
            ->success()
            ->send();

        $this->clearQuestion();
    }

    public function clearQuestion()
    {
        // dd('Velu');
        $this->description = '';
        $this->day = '';
    }

    public function editQuestion($id)
    {
        $this->a = Question::find($id);
        // dd($a->time);
        $this->description = $this->a->title;
        $this->status = $this->a->status;
        $this->day = $this->a->time;
        $this->day1 = $this->a->time;
        $this->updateVal = true;
        // dd($this->day1);

        // Convert 24-hour format to 12-hour format
        $dateTime = DateTime::createFromFormat('H:i:s', $this->day1);
        // // dd($dateTime);
        
        if ($dateTime) {
            $twelveHourTime = $dateTime->format('h:i A');
        } 

        $this->date = $twelveHourTime;

    }

    public function updateQuestion(){
        // dd($this->record);
        $updateQuestion = Question::find($this->record);
        // dd($updateQuestion);
        $updateQuestion->update([
            'title' => $updateQuestion->title,
            'status' => $updateQuestion->status,
            'time' => $updateQuestion->time,
        ]);
        // dd($updateQuestion->title);

        Notification::make()
            ->title('Updated successfully')
            ->success()
            ->send();
    }

    public function mount(): void
    {

        $this->users = User::all();
        // dd($this->users);
        $this->dailys = ['Daily On', 'Once a Week', 'Every other week', 'Once a month on the first'];
        $this->times = [['Beginning of the day (10:00 AM )','10:00 AM'], ['End of the day (06:00 PM )','06:00 PM']];
        $this->form->fill();

        if ($this->record != null) {
            $this->editQuestion($this->record);
        }

        static::authorizeResourceAccess();
    }
}
