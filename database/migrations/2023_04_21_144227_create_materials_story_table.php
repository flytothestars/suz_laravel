<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMaterialsStoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('materials_story', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('material_id')->index('materials_story_material_id_foreign');
            $table->string('owner_id')->nullable();
            $table->unsignedInteger('author_id')->nullable()->index('materials_story_author_id_foreign');
            $table->unsignedInteger('stock_id')->nullable()->index('materials_story_stock_id_foreign');
            $table->integer('qty')->nullable();
            $table->unsignedInteger('request_id')->nullable()->index('materials_story_request_id_foreign');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->integer('id_flow')->nullable();
            $table->boolean('returned')->nullable();
            $table->boolean('incoming')->nullable();
            $table->string('from', 255)->nullable();
            $table->string('from_stock', 255)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('materials_story');
    }
}
