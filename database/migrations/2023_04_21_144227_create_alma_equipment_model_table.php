<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlmaEquipmentModelTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alma_equipment_model', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_equipment_model')->index('id_equipment_model');
            $table->string('v_name');
            $table->string('v_vendor');
            $table->unsignedInteger('id_equipment_type');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alma_equipment_model');
    }
}
