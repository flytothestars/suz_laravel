<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSuzRequestStoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('suz_request_story', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('request_id')->index('request_id');
            $table->integer('id_flow')->index('id_flow')->comment('Номер заказа в АСР Forward');
            $table->integer('id_ci_flow')->nullable()->index('id_ci_flow')->comment('Код типа заказа в АСР Forward');
            $table->dateTime('dt_flow_dt_event')->nullable()->comment('Системная дата текущего вызова метода CreateFlow');
            $table->string('v_department')->nullable()->index('v_department')->comment('Филиал');
            $table->string('v_contract')->nullable()->comment('Номер контракта');
            $table->integer('id_location')->nullable()->comment('Участок');
            $table->integer('id_sector')->nullable()->comment('Сектор');
            $table->integer('id_region')->nullable()->comment('Область');
            $table->integer('id_district')->nullable()->comment('Район');
            $table->integer('id_town')->nullable()->comment('Город');
            $table->integer('b_structured_address')->nullable()->comment('Признак адреса. Допустимые значения:\\n
                0 – улица и дом могут быть неструктурированными.\\n
                1- полностью структурированный адрес');
            $table->integer('id_street')->nullable()->comment('Улица. Для b_structured_address = 0 заполняется кодом фиктивной улицы.');
            $table->integer('id_house')->nullable()->comment('Дом. Для b_structured_address = 0 заполняется кодом фиктивного дома');
            $table->string('v_flat')->nullable()->comment('Квартира/офис');
            $table->string('v_unstr_street')->nullable()->comment('Строковый параметр для улицы неструктурированного адреса. Для b_structured_address = 1 не заполняется
            Для b_structured_address = 0 обязательный для заполнения.');
            $table->string('v_unstr_house')->nullable()->comment('Строковый параметр для дома неструктурированного адреса. Для b_structured_address = 1 не заполняется
            Для b_structured_address = 0 обязательный для заполнения.');
            $table->string('v_client_title')->nullable()->comment('ФИО клиента, название организации юр. лица');
            $table->string('v_client_cell_phone')->nullable()->comment('Список сотовых телефонов клиента через разделитель «;»');
            $table->string('v_client_landline_phone')->nullable()->comment('Список городских телефонов клиента через разделитель «;»');
            $table->integer('id_kind_works')->nullable()->comment('Тип работ. Значение ID_KIND_WORK_INST из таблицы ALMA_KIND_WORKS');
            $table->text('ltype_works')->nullable()->comment('Список видов работ. Значение ID_TYPE_WORK из таблицы ALMA_TYPE_WORKS');
            $table->string('v_flow_descr')->nullable()->comment('Описание заявки, комментарий (Примечание к заказу)');
            $table->string('v_flow_time_descr')->nullable()->comment('Примечание к интервалу выполнения работ.');
            $table->date('dt_plan_date')->nullable()->comment('Запланированная дата выполнения работ. Не может быть раньше начала текущих суток');
            $table->integer('n_plan_time')->nullable()->comment('Запланированный интервал выполнения работ. Варианты значений:\\n0 - «До обеда»\\n1- «После обеда»');
            $table->integer('id_product')->nullable()->comment('Продукт. Значение id_product из таблицы fw_product');
            $table->integer('id_tplan')->nullable()->comment('Тарифный план. Значение id_tariff_plan из таблицы fw_tariff_plan');
            $table->string('v_client_switch_port')->nullable()->comment('Порт клиента на оборудовании MetroEthernet');
            $table->string('v_client_switch_mac')->nullable()->comment('Мак-адрес коммутатора MetroEthernet');
            $table->string('v_iin')->nullable()->comment('ИИН');
            $table->integer('id_document_type')->nullable()->comment('Тип документа. Значение из справочника FW_DOCUMENT_TYPES.');
            $table->string('v_document_number')->nullable()->comment('Номер документа');
            $table->date('dt_document_issue_date')->nullable()->comment('Номер документа');
            $table->string('v_document_series')->nullable()->comment('Серия документа');
            $table->date('dt_birthday')->nullable()->comment('Дата рождения. Должна соответствовать правилу 18-105 лет.');
            $table->text('service_info')->nullable()->comment('Информация по услугам');
            $table->integer('status_id')->index('status_id');
            $table->dateTime('dt_start')->index('dt_start');
            $table->dateTime('dt_stop')->default('2555-01-01 00:00:00');
            $table->integer('dispatcher_id')->index('dispatcher_id');
            $table->integer('installer_1')->nullable()->index('installer_1');
            $table->integer('installer_2')->nullable()->index('installer_2');
            $table->dateTime('date');
            $table->text('reason')->nullable();
            $table->integer('cancel_reason_id')->nullable()->comment('ID причины отмены');
            $table->text('comment')->nullable();
            $table->integer('routelist_id')->nullable();
            $table->integer('comment_author')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('suz_request_story');
    }
}
