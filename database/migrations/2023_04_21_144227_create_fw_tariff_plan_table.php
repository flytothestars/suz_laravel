<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFwTariffPlanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fw_tariff_plan', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_tariff_plan')->index('id_tariff_plan');
            $table->string('v_name');
            $table->integer('id_product');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fw_tariff_plan');
    }
}
