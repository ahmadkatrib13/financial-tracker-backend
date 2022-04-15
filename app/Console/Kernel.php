<?php

namespace App\Console;

use App\Models\Recurring;
use App\Models\Transaction;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // $schedule->command('inspire')->hourly();
        $schedule->call(function () {
            $now = Carbon::now();
            $recurrings = Recurring::WhereDate('next_run','<=',DB::raw('end_date'))->whereDate('next_run','<=',$now->toDateString())->get();
            $transactions=[];
            foreach($recurrings as $recurring){

                $min_now_enddate = Carbon::createFromFormat('Y-m-d', $recurring->end_date)->minimum(Carbon::now());
                $period = CarbonPeriod::create($recurring->next_run, $recurring->duration . ' ' . $recurring->schedule, $min_now_enddate);
                foreach ($period as $date) {
                    $transaction = new Transaction;
                    $transaction->title = $recurring->title;
                    $transaction->description = $recurring->description;
                    $transaction->amount = $recurring->amount;
                    $transaction->currency = $recurring->currency;
                    $transaction->date_time = $date->toDateString();
                    $transaction->recurring_id = $recurring->id;
                    $transaction->category_id = $recurring->category_id;
                    $transaction->save();
                    $recurring->next_run = $date->add($recurring->duration,$recurring->schedule);
                    $recurring->save();
                    array_push($transactions, $transaction);

                }
            }
        })->daily();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
