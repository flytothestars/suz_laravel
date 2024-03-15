<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Notifications\MaterialLimitExceeded;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendMaterialLimitNotification extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'send:material-limit-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send material limit statistics notification to administration';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $statisticData = getStatisticData();

        if (empty($statisticData)) {
            $this->info('Material limit data is empty, notification would not be send!');
            Log::info('Material limit data is empty, notification would not be send!');
            die('StatisticNotification for material limit was not sent, see logs');
        }

        $users = User::whereIn('id', [1170,5])->get(); //TODO добавить получателей, а именно Начальник участка, инженер, кладовщик, тех дир

        try {
            foreach ($users as $user) {
                $user->notify(new MaterialLimitExceeded($statisticData));
            }
        } catch (\Exception $exception) {
            Log::error('Something wrong with notify users!', ['trace' => $exception->getTraceAsString()]);
            $this->error('Something wrong with notify users!');
            die('StatisticNotification for material limit was not sent, see logs');
        }

        $this->info('Material limit notifications sent successfully!');
    }
}
