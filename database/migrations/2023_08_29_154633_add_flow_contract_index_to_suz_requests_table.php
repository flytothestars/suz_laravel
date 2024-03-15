<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddFlowContractIndexToSuzRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('suz_requests', function (Blueprint $table) {
            $table->index('id_flow');
            $table->index('v_contract');
            $table->index('id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('suz_requests', function (Blueprint $table) {
            $table->dropIndex('id_flow');
            $table->dropIndex('v_contract');
            $table->dropIndex('id');
        });
    }
}
