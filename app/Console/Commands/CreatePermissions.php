<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;

class CreatePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:permission {--name=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Создает разрешение';

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
        if ($name) {
            Permission::create(['name' => $name]);
            echo "Permission '$name' created successfully.";
        } else {
            echo "There is no required 'name' parameter. Please provide it and try again.";
        }
    }
}
