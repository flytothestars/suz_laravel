<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class AssignSuperAdminRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'assign:role';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Присваивает роль супер-администратора.';

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
     * @return mixed
     */
    public function handle()
    {
        $user = User::find(1);
        if($user->assignRole('супер-администратор'))
        {
            echo "Теперь у вас роль супер-администратора.";
        }
    }
}
