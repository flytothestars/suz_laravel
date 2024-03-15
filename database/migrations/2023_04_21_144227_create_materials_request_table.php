<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaterialsRequestTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('materials_request', function (Blueprint $table) {
            $table->integer('id', true);
            $table->integer('material_id');
            $table->integer('user_id')->nullable();
            $table->string('username')->nullable();
            $table->integer('author_id')->nullable();
            $table->string('authorname')->nullable();
            $table->integer('stock_id')->nullable();
            $table->string('stockname')->nullable();
            $table->integer('qty')->nullable();
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
        Schema::dropIfExists('materials_request');
    }
}
