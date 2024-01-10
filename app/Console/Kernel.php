<?php

namespace App\Console;

use App\Models\Date;
use App\Models\TaskUser;
use Filament\Notifications\Notification;
use Filament\Notifications\Actions\Action;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    public $dailyOn;
    public $onceWeek;
    public $onceMonth;

    protected function schedule(Schedule $schedule): void
    {
       
        // $schedule->command('inspire')->hourly();
        $schedule->call(function () {
            Date::create([
                'date' => now()
            ]);
        })->dailyAt('01:00');

        // Daily On 
        $schedule->call(function () {
            $taskUsers = TaskUser::with('users', 'question')
                ->whereHas('question', function ($query) {
                    $query->where('status', 'Daily On');
                })
                ->get();
            if ($taskUsers[0]->question->status) {
                foreach ($taskUsers as $taskUser) {
                    $sendNotification = $taskUser->users;
                    Notification::make()
                        ->success()
                        ->title($taskUser->question->title)
                        ->actions([
                            Action::make('view')
                                ->button()
                                ->url("/dates/popup"),
                        ])
                        ->sendToDatabase($sendNotification);
                }
            }
            $this->dailyOn = $taskUsers[0]->question->time;
            // dd($this->dailyOn);
        })->dailyAt($this->dailyOn);

        // Once a Week
        $schedule->call(function () {
            $taskUsers = TaskUser::with('users', 'question')
                ->whereHas('question', function ($query) {
                    $query->where('status', 'Once a Week');
                })
                ->get();
            if ($taskUsers[0]->question->status) {
                foreach ($taskUsers as $taskUser) {
                    $sendNotification = $taskUser->users;
                    Notification::make()
                        ->success()
                        ->title($taskUser->question->title)
                        ->actions([
                            Action::make('view')
                                ->button()
                                ->url("/dates/popup"),
                        ])
                        ->sendToDatabase($sendNotification);
                }
            }
            $this->onceWeek = $taskUsers[0]->question->time;
        })->weeklyOn(1, $this->onceWeek);

        // Every other week
        $schedule->call(function () {
            $taskUsers = TaskUser::with('users', 'question')
                ->whereHas('question', function ($query) {
                    $query->where('status', 'Every other week');
                })
                ->get();
            if ($taskUsers[0]->question->status) {
                foreach ($taskUsers as $taskUser) {
                    $sendNotification = $taskUser->users;
                    Notification::make()
                        ->success()
                        ->title($taskUser->question->title)
                        ->actions([
                            Action::make('view')
                                ->button()
                                ->url("/dates/popup"),
                        ])
                        ->sendToDatabase($sendNotification);
                }
            }
        })->everyMinute();

        // Once a month on the first
        $schedule->call(function () {
            $taskUsers = TaskUser::with('users', 'question')
                ->whereHas('question', function ($query) {
                    $query->where('status', 'Once a month on the first');
                })
                ->get();
            if ($taskUsers[0]->question->status) {
                foreach ($taskUsers as $taskUser) {
                    $sendNotification = $taskUser->users;
                    Notification::make()
                        ->success()
                        ->title($taskUser->question->title)
                        ->actions([
                            Action::make('view')
                                ->button()
                                ->url("/dates/popup"),
                        ])
                        ->sendToDatabase($sendNotification);
                }
            }
            $this->onceMonth = $taskUsers[0]->question->time;

        })->monthly($this->onceMonth);

    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
