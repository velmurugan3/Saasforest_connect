<?php

namespace App\Console;

use App\Models\Date;
use App\Models\TaskUser;
use DateTime;
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
    public $trimmed_time;
    public $trimmed_time2;

    protected function schedule(Schedule $schedule): void
    {

        // $schedule->command('inspire')->hourly();
        $schedule->call(function () {
            Date::create([
                'date' => now()
            ]);
        })->dailyAt('01:00');


        // Daily On 
        $dailyTaskUsers = TaskUser::with('users', 'question')
            ->whereHas('question', function ($query) {
                $query->where('status', 'Daily On');
            })
            ->get();

        foreach ($dailyTaskUsers as $taskUser) {
            $this->dailyOn = $taskUser->question->time;
            $time_obj = DateTime::createFromFormat('H:i:s', $this->dailyOn);
            $trimmed_time = $time_obj->format('H:i');

            $schedule->call(function () use ($taskUser) {
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
            })->dailyAt($trimmed_time);
        }


        // Once a Week
        $weekTaskUsers = TaskUser::with('users', 'question')
        ->whereHas('question', function ($query) {
            $query->where('status', 'Once a Week');
        })
        ->get();

        foreach ($weekTaskUsers as $taskUser) {
            $this->onceWeek = $taskUser->question->time;
            $time_obj = DateTime::createFromFormat('H:i:s', $this->onceWeek);
            $trimmed_time = $time_obj->format('H:i');

            $schedule->call(function () use ($taskUser) {
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
            })->weeklyOn(1, $trimmed_time);
        }


        // Once a month on the first
        $monthTaskUsers = TaskUser::with('users', 'question')
        ->whereHas('question', function ($query) {
            $query->where('status', 'Once a month on the first');
        })
        ->get();

        foreach ($monthTaskUsers as $taskUser) {
            $this->onceMonth = $taskUser->question->time;
            $time_obj = DateTime::createFromFormat('H:i:s', $this->onceMonth);
            $trimmed_time = $time_obj->format('H:i');

            $schedule->call(function () use ($taskUser) {
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
            })->monthly($trimmed_time);
        }

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
