<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRequestRouteListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('request_route_list', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('request_id')->index('request_id')->comment('ID заявки');
            $table->integer('routelist_id')->index('routelist_id')->comment('ID маршрутного листа');
            $table->dateTime('time')->index('time')->comment('Дата и время заявки');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('request_route_list');
    }
}
