<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToEquipmentStoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('equipment_story', function (Blueprint $table) {
            $table->foreign(['author_id'])->references(['id'])->on('users')->onUpdate('NO ACTION')->onDelete('NO ACTION');
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
        Schema::table('equipment_story', function (Blueprint $table) {
            $table->dropForeign('equipment_story_author_id_foreign');
            $table->dropForeign('equipment_story_stock_id_foreign');
        });
    }
}
