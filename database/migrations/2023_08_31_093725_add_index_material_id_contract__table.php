<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexMaterialIdContractTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_material', function (Blueprint $table) {
            $table->index('material_id');
            $table->index('contract');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('client_material', function (Blueprint $table) {
            $table->dropIndex('material_id');
            $table->dropIndex('contract');
        });
    }
}
