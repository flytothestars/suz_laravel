<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKitRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kit_request', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('kit_id');
            $table->string('v_serial', 255);
            $table->integer('author_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('username')->nullable();
            $table->integer('stock_id')->nullable();
            $table->string('stockname')->nullable();
            $table->integer('from_stock')->nullable();
            $table->string('from_stockname')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kit_request');
    }
}
