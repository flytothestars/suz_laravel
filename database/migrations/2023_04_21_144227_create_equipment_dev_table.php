<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEquipmentDevTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('equipment_dev', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_equipment_model')->index('id_equipment_model')->comment('Внутренний код модели оборудования в АСР Forward');
            $table->integer('id_equipment_inst')->comment('Внутренний код экземпляра оборудования в АСР Forward');
            $table->string('v_equipment_number', 255)->comment('Серийный номер экземпляра оборудования');
            $table->unsignedInteger('kit_id')->index('equipment_kit_id_foreign');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->string('stock_id')->nullable();
            $table->integer('owner_id')->nullable();
            $table->boolean('returned')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('equipment_dev');
    }
}
