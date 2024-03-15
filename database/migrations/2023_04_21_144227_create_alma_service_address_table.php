<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlmaServiceAddressTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alma_service_address', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_service_ad');
            $table->integer('id_sector');
            $table->integer('id_technology');
            $table->integer('id_house');
            $table->integer('id_service');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alma_service_address');
    }
}
