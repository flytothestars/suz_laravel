<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockMaterialTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_material', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('material_id')->index('stock_material_material_id_foreign');
            $table->unsignedInteger('stock_id')->index('stock_material_stock_id_foreign');
            $table->integer('qty');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_material');
    }
}
