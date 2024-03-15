<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFwAddressLigamentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fw_address_ligament', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_house')->index('id_house');
            $table->string('house_nm');
            $table->integer('id_region');
            $table->integer('id_district');
            $table->integer('id_town');
            $table->integer('id_street');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('fw_address_ligament');
    }
}
