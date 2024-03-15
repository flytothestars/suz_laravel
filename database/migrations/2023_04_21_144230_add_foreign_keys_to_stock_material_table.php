<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToStockMaterialTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('stock_material', function (Blueprint $table) {
            $table->foreign(['material_id'])->references(['id'])->on('materials')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['stock_id'])->references(['id'])->on('stocks')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('stock_material', function (Blueprint $table) {
            $table->dropForeign('stock_material_material_id_foreign');
            $table->dropForeign('stock_material_stock_id_foreign');
        });
    }
}
