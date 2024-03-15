<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $statuses = array(
            ["name" => "Новый"],
            ["name" => "Отложено"],
            ["name" => "Отменено"],
            ["name" => "Назначено"],
            ["name" => "Выполнено"],
            ["name" => "Договорено"],
        );
        DB::table("statuses")->truncate();
        DB::table("statuses")->insert($statuses);
    }
}
