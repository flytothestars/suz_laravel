<?php

namespace App\Console\Commands;

use App\Models\RepairCount;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ClearRepairCount extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clear:repairCount';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clearing count field for repair_count table.';

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
        DB::beginTransaction();
        try {
            RepairCount::query()->update(['count' => 0]);
            DB::commit();
        } catch (\Exception $exception) {
            Log::error($exception->getMessage(), ['data' => $exception->getTraceAsString()]);
            DB::rollBack();
        }

        $this->info('Repair count cleared successfully!');
    }
}
