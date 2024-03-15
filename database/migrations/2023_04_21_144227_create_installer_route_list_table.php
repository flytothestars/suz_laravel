<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInstallerRouteListTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('installer_route_list', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('installer_1')->nullable()->index('installer_1')->comment('ID первого монтажника');
            $table->integer('installer_2')->nullable()->index('installer_2')->comment('ID второго монтажника');
            $table->integer('routelist_id')->index('routelist_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('installer_route_list');
    }
}
