<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOltTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('olt', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_house');
            $table->foreign('id_house')->references(['id_house'])->on('fw_address_ligament')->OnDelete('cascade');
            $table->string('host_name_olt');
            $table->integer('port_olt');
            $table->integer('ip_address');
            $table->string('login');
            $table->string('pass');
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
        Schema::dropIfExists('olt');
    }
}
