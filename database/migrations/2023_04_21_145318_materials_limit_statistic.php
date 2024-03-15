<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MaterialsLimitStatistic extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('materials_limit_statistic', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('request_id')->unsigned();
            $table->integer('material_id')->unsigned();
            $table->integer('installer_id')->unsigned();
            $table->integer('qty');

            $table->foreign('request_id')->references(['id'])->on('suz_requests')->OnDelete('cascade');
            $table->foreign('material_id')->references(['id'])->on('materials')->OnDelete('cascade');
            $table->foreign('installer_id')->references(['id'])->on('users')->OnDelete('cascade');

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
        Schema::dropIfExists('materials_limit_statistic');
    }
}
