<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuzRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suz_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('id_flow')->comment('Номер заказа в АСР Forward');
            $table->integer('id_ci_flow')->comment('Код типа заказа в АСР Forward');
            $table->dateTime('dt_flow_dt_event')->comment('Системная дата текущего вызова метода CreateFlow');
            $table->string('v_department')->comment('Филиал');
            $table->string('v_contract')->comment('Номер контракта');
            $table->integer('id_location')->comment('Участок');
            $table->integer('id_sector')->comment('Сектор');
            $table->integer('id_region')->comment('Область');
            $table->integer('id_district')->comment('Район');
            $table->integer('id_town')->comment('Город');
            $table->integer('b_structured_address')->comment('Признак адреса. Допустимые значения:

                0 – улица и дом могут быть неструктурированными.

                1- полностью структурированный адрес');
            $table->integer('id_street')->comment('Улица. Для b_structured_address = 0 заполняется кодом фиктивной улицы.');
            $table->integer('id_house')->comment('Дом. Для b_structured_address = 0 заполняется кодом фиктивного дома');
            $table->string('v_flat')->comment('Квартира/офис');
            $table->string('v_unstr_street')->comment('Строковый параметр для улицы неструктурированного адреса. Для b_structured_address = 1 не заполняется
            Для b_structured_address = 0 обязательный для заполнения.');
            $table->string('v_unstr_house')->comment('Строковый параметр для дома неструктурированного адреса. Для b_structured_address = 1 не заполняется
            Для b_structured_address = 0 обязательный для заполнения.');
            $table->text('v_client_title')->comment('ФИО клиента, название организации юр. лица');
            $table->string('v_client_cell_phone')->comment('Список сотовых телефонов клиента через разделитель «;»');
            $table->string('v_client_landline_phone')->comment('Список городских телефонов клиента через разделитель «;»');
            $table->integer('id_kind_works')->comment('Тип работ. Значение ID_KIND_WORK_INST из таблицы ALMA_KIND_WORKS');
            $table->text('ltype_works')->comment('Список видов работ. Значение ID_TYPE_WORK из таблицы ALMA_TYPE_WORKS');
            $table->text('v_flow_descr')->comment('Описание заявки, комментарий (Примечание к заказу)');
            $table->text('v_flow_time_descr')->comment('Примечание к интервалу выполнения работ.');
            $table->date('dt_plan_date')->comment('Запланированная дата выполнения работ. Не может быть раньше начала текущих суток');
            $table->integer('n_plan_time')->comment('Запланированный интервал выполнения работ. Варианты значений:
0 - «До обеда»
1- «После обеда»');
            $table->integer('id_product')->comment('Продукт. Значение id_product из таблицы fw_product');
            $table->integer('id_tplan')->comment('Тарифный план. Значение id_tariff_plan из таблицы fw_tariff_plan');
            $table->string('v_client_switch_port')->comment('Порт клиента на оборудовании MetroEthernet');
            $table->string('v_client_switch_mac')->comment('Мак-адрес коммутатора MetroEthernet');
            $table->string('v_iin')->comment('ИИН');
            $table->integer('id_document_type')->nullable()->comment('Тип документа. Значение из справочника FW_DOCUMENT_TYPES.');
            $table->string('v_document_number', 255)->comment('Номер документа');
            $table->date('dt_document_issue_date')->nullable()->comment('Номер документа');
            $table->string('v_document_series')->comment('Серия документа');
            $table->date('dt_birthday')->nullable()->comment('Дата рождения. Должна соответствовать правилу 18-105 лет.');
            $table->text('service_info')->comment('Информация по услугам');
            $table->integer('status_id')->comment('Внутренний статус заявки');
            $table->dateTime('dt_start');
            $table->dateTime('dt_stop')->default('2555-01-01 00:00:00');
            $table->integer('routelist_id')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('suz_requests');
    }
}
