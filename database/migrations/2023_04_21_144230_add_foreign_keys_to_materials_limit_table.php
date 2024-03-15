<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToMaterialsLimitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('materials_limit', function (Blueprint $table) {
            $table->foreign(['material_id'], 'materials_limit_materials_id_fk')->references(['id'])->on('materials')->onUpdate('CASCADE')->onDelete('CASCADE');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('materials_limit', function (Blueprint $table) {
            $table->dropForeign('materials_limit_materials_id_fk');
        });
    }
}
