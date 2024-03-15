<?php

namespace App\Http\Controllers\API\Catalogs;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

class WorkTypeController extends Controller
{
    // TODO Сделать вывод через ресурс-коллекшн класс
    public function index()
    {

        return response()->json(['data' => DB::table("alma_kind_works")->whereNotIn('id_kind_work_inst', [2010, 2006, 2007, 2008, 2009])->distinct('id_kind_work_inst')->orderBy('v_name', 'ASC')->get()]);
    }

    public function group()
    {
        return response()->json([
            'data' => [
                [
                    "id" => "1","name" => "Подключение", 
                    "child" => [
                        ["id" => "2425", "name" => "Подключение услуг по новому адресу"],
                        ["id" => "2402", "name" => "Подключение услуги"],
                        ["id" => "2440", "name" => "Подключение доп. точки"],
                    ]
                ],
                [
                    "id" => "2","name" => "Работы с блокировками", 
                    "child" => [
                        ["id" => "2424", "name" => "Отключение услуг по старому адресу"],
                        ["id" => "2409", "name" => "Блокировка услуг (задолженность) с выездом"],
                        ["id" => "2400", "name" => "Блокировка услуг с выездом"],
                        ["id" => "2411", "name" => "Разблокировка услуг с выездом"],
                    ]
                ],
                [
                    "id" => "3","name" => "Сервисные работы", 
                    "child" => [
                        ["id" => "2403", "name" => "Смена ТП/продукта/основного пакета"],
                        ["id" => "2410", "name" => "Смена технологии на существующем адресе"],
                        ["id" => "2404", "name" => "Ремонт"]]
                ],
                [
                    "id" => "4","name" => "Демонтаж/Расторжения", 
                    "child" => [
                        ["id" => "2401", "name" => "Демонтаж оборудования"],
                        ["id" => "2407", "name" => "Расторжение"]]
                ],
            ]
        ]);
    }
}
