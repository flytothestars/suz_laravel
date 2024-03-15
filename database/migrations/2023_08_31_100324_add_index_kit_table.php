<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexKitTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('kit', function (Blueprint $table) {
            $table->index('owner_id');
            $table->index('v_department');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('kit', function (Blueprint $table) {
            $table->dropIndex('owner_id');
            $table->dropIndex('v_department');
        });
    }
}
