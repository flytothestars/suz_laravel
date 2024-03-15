<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddForeignKeysToMaterialsStoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('materials_story', function (Blueprint $table) {
            $table->foreign(['author_id'])->references(['id'])->on('users')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['material_id'])->references(['id'])->on('materials')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['stock_id'])->references(['id'])->on('stocks')->onUpdate('NO ACTION')->onDelete('NO ACTION');
            $table->foreign(['request_id'], 'materials_story_suz_requests_id_fk')->references(['id'])->on('suz_requests')->onUpdate('NO ACTION')->onDelete('NO ACTION');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('materials_story', function (Blueprint $table) {
            $table->dropForeign('materials_story_author_id_foreign');
            $table->dropForeign('materials_story_material_id_foreign');
            $table->dropForeign('materials_story_stock_id_foreign');
            $table->dropForeign('materials_story_suz_requests_id_fk');
        });
    }
}
