<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserMaterialTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_material', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('material_id')->index('user_material_material_id_foreign');
            $table->unsignedInteger('user_id')->index('user_material_user_id_foreign');
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
        Schema::dropIfExists('user_material');
    }
}
