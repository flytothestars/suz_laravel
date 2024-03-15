<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddIndexRequestIdTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('suz_request_dispatcher', function (Blueprint $table) {
            $table->index('dispatcher_id');
            $table->index('request_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('suz_request_dispatcher', function (Blueprint $table) {
            $table->dropIndex('dispatcher_id');
            $table->dropIndex('request_id');
        });
    }
}
