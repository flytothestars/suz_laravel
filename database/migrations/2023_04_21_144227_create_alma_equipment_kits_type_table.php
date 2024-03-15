<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlmaEquipmentKitsTypeTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alma_equipment_kits_type', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_equip_kits_type');
            $table->string('v_name');
            $table->string('v_mnemonic');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alma_equipment_kits_type');
    }
}
