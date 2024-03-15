<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class CreateRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:roles {--name=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создает роли';

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
        $name = $this->option('name');
        if($name)
        {
            Role::create(['name' => $name]);
            echo "Роль создана.";
        }
        else
        {
            Role::create(['name' => 'администратор']);
            Role::create(['name' => 'диспетчер']);
            Role::create(['name' => 'техник']);
            Role::create(['name' => 'кладовщик']);
            Role::create(['name' => 'супервизор']);
            Role::create(['name' => 'супер-администратор']);
            echo "Роли созданы.";
        }
    }
}
