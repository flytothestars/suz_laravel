<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAlmaTypeWorksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('alma_type_works', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_type_work');
            $table->string('v_name');
            $table->unsignedInteger('id_kind_work_inst');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('alma_type_works');
    }
}
