<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKitDevTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kit_dev', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_flow')->comment('Номер заказа в АСР Forward');
            $table->string('v_type')->comment('Мнемоника типа комплекта');
            $table->integer('id_kits')->comment('Внутренний код комплекта оборудования в АСР Forward');
            $table->string('v_serial', 255)->comment('Номер комплекта оборудования');
            $table->string('v_department')->comment('Филиал. Значение v_ext_ident из таблицы fw_departments');
            $table->string('id_status')->comment('Состояние комплекта. Внутренний код из таблицы ALMA_STATUS_EQ_KITS');
            $table->date('dt_activate')->comment('Дата активации комплекта');
            $table->date('dt_plan_deactivate')->nullable()->comment('Плановая дата деактивации комплекта');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->string('owner_id')->nullable();
            $table->integer('stock_id')->nullable();
            $table->boolean('returned')->default(false);
            $table->string('source')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('kit_dev');
    }
}
