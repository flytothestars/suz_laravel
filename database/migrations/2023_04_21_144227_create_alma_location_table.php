<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlmaLocationTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alma_location', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_location')->index('id_location');
            $table->string('v_name');
            $table->integer('id_town')->nullable();
            $table->integer('id_department');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alma_location');
    }
}
