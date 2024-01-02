<?php

namespace App\Filament\Resources\DailyWorkResource\Pages;

use App\Filament\Resources\DailyWorkResource;
use App\Models\DailyWork;
use App\Models\Date;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Page;


class Work extends Page implements HasForms
{

    use InteractsWithForms;

    protected static string $resource = DailyWorkResource::class;

    protected static string $view = 'filament.resources.daily-work-resource.pages.work';
    public $record;
    public $dailyworks;
    public $content;
    public $currentData;
    public $auth;
    public $dateValue;
    public $workId;
    public $firstLetterUpper;
    public ?array $work = [];
    public ?array $daily = [];


    public function workForm(Form $form): Form
    {
        return $form
            ->schema([
                RichEditor::make('content')
                    ->label('')
                    ->required()
                    ->columnSpanFull(),
            ])
            ->statePath('work')
            ->model(DailyWork::class);
    }

    public function dailyForm(Form $form): Form
    {
        return $form
            ->schema([
                RichEditor::make('content')
                    ->label('')
                    ->required()
                    ->columnSpanFull()
                    ->default($this->currentData),
            ])
            ->statePath('daily')
            ->model(DailyWork::class);
    }

    protected function getForms(): array
    {
        return [
            'workForm',
            'dailyForm',
        ];
    }

    public function createWork()
    {
        //  dd($this->workForm->getState());
        $this->auth = auth()->user()->id;
        // dd($auth);
        $this->content = $this->workForm->getState();
        // dd($this->content['content']);
        // dd($this->record);

        // dd($a);

        $work = DailyWork::create([
            'user_id' => $this->auth,
            'date_id' => $this->record,
            'content' => $this->content['content'],
        ]);

        Notification::make()
            ->title('Saved successfully')
            ->success()
            ->send();

        // $this->dailyworks = DailyWork::with('user')->orderBy('created_at', 'desc')->get();

        $this->dailyworks = DailyWork::with('user', 'date')->where('date_id', $this->record)->orderBy('created_at', 'desc')->get();
        // dd($this->dailyworks);

        $this->workForm->fill();

        $this->resetForm();

    }

    public function resetForm()
    {

        $this->refresh();
    }

    public function refresh()
    {
        // Logic to refresh the page or component
    }

    public function editWork($id)
    {
        // dd($id);
        $this->workId = $id;
        $test = DailyWork::find($id);
        $this->currentData = $id;
        $this->currentData = $test->user_id;
        $this->currentData = $test->content;
        // dd($this->currentData);
        // $this->workForm->fill();
        $this->dailyForm->fill();

        $this->dailyworks = DailyWork::with('user', 'date')->where('date_id', $this->record)->orderBy('created_at', 'desc')->get();
    }

    public function updateWork()
    {
        $updateWork = DailyWork::find($this->workId);
        $workId = $this->daily;
        // dd($updateWork);
        // dd($updateWork->user_id);
        // dd($updateWork->content);
        $updateWork->update([
            'user_id' => $updateWork->user_id,
            'content' => $workId['content'],
        ]);

        Notification::make()
            ->title('Updated successfully')
            ->success()
            ->send();

        $this->dailyworks = DailyWork::with('user', 'date')->where('date_id', $this->record)->orderBy('created_at', 'desc')->get();
    }

    public function mount(): void
    {
        $this->dailyworks = DailyWork::with('user.jobInfo.designation', 'date')->where('date_id', $this->record)->orderBy('created_at', 'desc')->get();
        // dd($this->dailyworks[0]->user->jobInfo->designation->name);
        // dd($this->dailyworks);
        // dd($this->dailyworks[0]->created_at);
        // dd($this->dailyworks[0]->user->id);
        $this->workForm->fill();
        $this->dailyForm->fill();
        static::authorizeResourceAccess();
    }
}
