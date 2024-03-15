<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;

class ImportCatalogs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:catalogs {--table=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Импорт справочников из файлов';

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
        $tableNames = array(
            'alma_conclusion_source',
            'alma_dealers',
            'alma_equipment_kits_type',
            'alma_equipment_type',
            'alma_equipment_model',
            'alma_grid_fill_eq_kits_type',
            'alma_kind_works',
            'alma_type_works',
            'alma_list_agent',
            'alma_location',
            'alma_offer',
            'alma_office',
            'alma_partner_developer',
            'alma_reason_repair',
            'alma_sector',
            'alma_service_address',
            'alma_status_eq_kits',
            'alma_technology_type',
            'ci_bank',
            'ci_flow',
            'cii_client_type',
            'fw_address_ligament',
            'fw_address_ligament2',
            'fw_address_ligament3',
            'fw_address_ligament4',
            'fw_category',
            'fw_contract_type',
            'fw_departments',
            'fw_district',
            'fw_once_service',
            'fw_product',
            'fw_region',
            'fw_product_content',
            'fw_service',
            'fw_street',
            'fw_tariff_plan',
            'fw_town',
            'alma_reason_undo_flow',
            'alma_source_pay_debt',
            'fw_document_types'
        );
        $table = $this->option('table');
        if($table)
        {
            if(in_array($table, $tableNames))
            {
                $tableNames = array($table);
            }
            else
            {
                echo "No such table.";
                exit;
            }
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        foreach($tableNames as $name)
        {
            if($name == "fw_address_ligament2" || $name == "fw_address_ligament3" || $name == "fw_address_ligament4")
            {
                continue;
            }
            DB::table($name)->truncate();
        }
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        foreach($tableNames as $name)
        {
            echo "[" . $name . "] importing...\n";
            if($name == "fw_address_ligament2" || $name == "fw_address_ligament3" || $name == "fw_address_ligament4")
            {
                $classname = "App\Imports\FwAddressLigamentImport";
            }
            else
            {
                $count = DB::table($name)->count();
                if($count > 0)
                {
                    echo "[$name] skipping...\n\n";
                    continue;
                }
                $classname = "App\Imports\\".str_replace(" ", "", ucwords(str_replace("_", " ", $name)))."Import";
            }
            Excel::import(new $classname, $name.'.xlsx', 'public');
            echo "[" . $name . "] is completed.\n\n";
        }
        echo 'Catalogs imported successfully!';
    }
}
