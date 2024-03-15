<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdwUsersTableInsertion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'insert:adw-users-table';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Truncation almatv_engineer table on adw database, and inserting users data from suz_db';

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
     * @throws \Throwable
     */
    public function handle()
    {
        $userData = User::select('id', 'name', 'email', 'username')->get();
        $insertData = $userData->map(function ($user) {
            return [
                'ID_USER' => $user->id,
                'V_FULL_NAME' => $user->name,
                'V_EMAIL' => $user->email,
                'V_USERNAME' => $user->username,
            ];
        });

        DB::beginTransaction();
        try {
            DB::connection('oracle')->statement('begin fw_data.trunc_almatv_engineer(); end;');
            DB::connection('oracle')->table('ALMATV_ENGINEER')->insert($insertData->toArray());
            DB::commit();
        } catch (\Exception $exception) {
            DB::rollBack();
            Log::error('Something wrong with inserting data into ADW!', ['trace' => $exception->getTraceAsString()]);
            $this->info('Users data insert failed!');
            die('Inserting Error!');
        }

        $this->info('Users data inserted successfully!');
    }
}
