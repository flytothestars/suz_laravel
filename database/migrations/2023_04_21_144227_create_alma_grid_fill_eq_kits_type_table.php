<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlmaGridFillEqKitsTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alma_grid_fill_eq_kits_type', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_grid_fill_eq_kits_type');
            $table->integer('id_equip_kits_type');
            $table->integer('id_equipment_type');
            $table->integer('id_equipment_model');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alma_grid_fill_eq_kits_type');
    }
}
