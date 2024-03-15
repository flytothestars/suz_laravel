<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentStoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipment_story', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('equipment_id')->index('equipment_id')->comment('ID комплекта или оборудования');
            $table->string('owner_id')->nullable()->comment('ID владельца или номер контракта');
            $table->unsignedInteger('author_id')->index('equipment_story_author_id_foreign')->comment('ID автора');
            $table->unsignedInteger('stock_id')->nullable()->index('equipment_story_stock_id_foreign')->comment('ID склада');
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->boolean('is_kit');
            $table->integer('id_flow')->nullable();
            $table->string('serial', 255)->nullable();
            $table->string('from', 255)->nullable();
            $table->string('from_stock', 255)->nullable();
            $table->string('v_kits_transfer')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('equipment_story');
    }
}
