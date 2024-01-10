<?php

namespace App\Filament\Resources\QuestionResource\Pages;

use App\Filament\Resources\QuestionResource;
use App\Models\Question;
use App\Models\TaskUser;
use App\Models\User;
use DateTime;
// use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Actions\Action;
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
    public $record;
    public $passValue;
    #[Validate('required')]
    public $description;

    #[Validate('required')]
    public $status;

    #[Validate('required')]
    public $day;

    public $userName;
    public ?array $data = [];
    public $a;
    public $date;
    public $selectTime;
    public $updateVal;
    public $idCollection = [];

    public function form(Form $form): Form
    {
        $options = [];
        // dd($this->idCollection);
        $taskUser = TaskUser::with('question')->where('question_id', $this->record)->get();
        foreach ($taskUser as $task) {
            $names = User::find($task->user_id);
            $this->idCollection[] = $names->name;
        }

        // dd($this->idCollection);
        if ($this->idCollection == []) {
            $options = User::all()->pluck('name', 'id');
        } else {
            // dd('Velu');
            $options = User::whereNotIn('id', $this->idCollection)->pluck('name', 'id');
        }

        return $form
            ->schema([
                Select::make('user_id')
                    ->label('Who do you want to ask?')
                    ->multiple()
                    ->options($options)
                    ->searchable()
                    ->required(),
            ])
            ->statePath('data');
    }

    protected function getForms(): array
    {
        return [
            'form',
        ];
    }

    public function createQuestion(): void
    {

        $this->validate();

        if ($this->day == 'Beginning of the day (10:00 AM )') {
            // dd('Velu');
            $this->day = '10:00';
            $question = Question::create([
                'title' => $this->description,
                'status' => $this->status,
                'time' => $this->day,
            ]);
        } elseif ($this->day == 'End of the day (06:00 PM )') {
            // dd('Muthu');
            $this->day = '18:00';
            $question = Question::create([
                'title' => $this->description,
                'status' => $this->status,
                'time' => $this->day,
            ]);
        } else {
            // dd('Velsamy');
            $question = Question::create([
                'title' => $this->description,
                'status' => $this->status,
                'time' => $this->day,
            ]);
        }
        // dd($question);

        $userData = $this->form->getState();
        // dd($userData['user_id']);

        $this->passValue = $this->description;

        foreach ($userData['user_id'] as $b) {
            TaskUser::create([
                'user_id' => $b,
                'question_id' => $question->id,
            ]);
            // $new = User::find($b); 
            // Notification::make()
            // ->success()
            // ->title($this->passValue)
            // ->actions([
            //     Action::make('view')
            //         ->button()
            //         ->url(route('filament.admin.resources.dates.index')),
            // ])
            // ->sendToDatabase($new);
        }

        Notification::make()
            ->title('Saved successfully')
            ->success()
            ->send();

        $this->clearQuestion();

    }

    public function editQuestion($id)
    {
        $this->a = Question::find($id);
        // dd($this->a);
        $taskUser = TaskUser::with('question')->where('question_id', $id)->get();
        // dd($taskUser);
        $this->description = $this->a->title;
        $this->status = $this->a->status;
        $this->day = $this->a->time;
        // $this->day1 = $this->a->time;
        $this->updateVal = true;
        $nameCollection = [];
        // dd($taskUser);
        foreach ($taskUser as $task) {
            $names = User::find($task->user_id);
            // dd($names);
            $nameCollection[] = $names->id;
            // dd($nameCollection);
        }
        // dd($nameCollection);
        $this->form->fill(['user_id' => $nameCollection]);
        // dd($nameCollection);
        // dd($userFill);
        // dd($this->day1);

        // Convert 24-hour format to 12-hour format
        $dateTime = DateTime::createFromFormat('H:i:s', $this->day);
        // dd($dateTime);

        if ($dateTime) {
            $twelveHourTime = $dateTime->format('h:i A');
        }

        $this->date = $twelveHourTime;
    }

    public function updateQuestion()
    {
        // dd($this->record);
        $updateQuestion = Question::find($this->record);
        // dd($updateQuestion->time);
        // dd($this->day);
        // $userData = $this->form->getState();
        // dd($userData);

        if ($this->day == 'Beginning of the day (10:00 AM )') {
            // dd('Velu');
            $this->day = '10:00';
            $updateQuestion->update([
                'title' => $this->description,
                'status' => $this->status,
                'time' => $this->day,
            ]);
        } elseif ($this->day == 'End of the day (06:00 PM )') {
            // dd('Muthu');
            $this->day = '18:00';
            $updateQuestion->update([
                'title' => $this->description,
                'status' => $this->status,
                'time' => $this->day,
            ]);
        } else {
            // dd('Velsamy');
            $updateQuestion->update([
                'title' => $this->description,
                'status' => $this->status,
                'time' => $this->day,
            ]);
        }

        $userData = $this->form->getState();
        // dd($userData['user_id']);

        foreach ($userData['user_id'] as $userId) {
            // Check if the user_id already exists
            $existingUser = TaskUser::where('user_id', $userId)->where('question_id', $updateQuestion->id)->first();
            // dd($existingUser);

            // If the user does not exist, perform the update
            if (!$existingUser) {
                TaskUser::create([
                    'user_id' => $userId,
                    'question_id' => $updateQuestion->id,
                ]);
            }

        }

        // Delete records for users that are in the database but not in the form state
        $v = TaskUser::whereNotIn('user_id', $userData['user_id'])->where('question_id', $updateQuestion->id)
            ->delete();
            // dd($v);

        Notification::make()
            ->title('Updated successfully')
            ->success()
            ->send();

        $this->clearQuestion();
    }

    public function clearQuestion()
    {

        $this->description = '';
        $this->status = '';
        $this->day = '';
        $this->selectTime = '';
        $this->form->fill();
        
    }

    public function mount(): void
    {

        $taskUser = TaskUser::with('question')
        ->whereHas('question', function ($query) {
            $query->where('status', 'Daily On');
        })
        ->get();
        // dd($taskUser[0]->question->status);
        $this->users = User::with('jobInfo.designation')->get();
        // dd($this->users[1]->jobInfo->designation->name);
        $this->dailys = ['Daily On', 'Once a Week', 'Every other week', 'Once a month on the first'];
        $this->times = [['Beginning of the day (10:00 AM )', '10:00 AM'], ['End of the day (06:00 PM )', '06:00 PM']];
        $this->form->fill();

        if ($this->record != null) {
            $this->editQuestion($this->record);
        }

        static::authorizeResourceAccess();
    }
}
