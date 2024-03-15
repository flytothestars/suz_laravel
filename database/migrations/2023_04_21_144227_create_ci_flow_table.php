<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCiFlowTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ci_flow', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_ci_flow')->index('id_ci_flow');
            $table->string('v_name')->index('v_name');
            $table->string('close_method_name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ci_flow');
    }
}
