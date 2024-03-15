<div class="modal-header">
    <h5 class="modal-title" id="myKitsModalLabel">{{ $direction == 'give' ? 'Выдать абоненту' : 'Забрать у абонента' }}</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <input type="hidden" value="{{ $service_key }}" name="service_key">
    <div class="form-group">
        <label>Направление:</label>
        <select name="direction" id="direction" class="form-control">
            <option {{ $direction == 'give' ? 'selected' : '' }} value="give">Выдать абоненту</option>
            <option {{ $direction == 'take' ? 'selected' : '' }} value="take">Забрать у абонента</option>
        </select>
    </div>
    <div class="form-group">
        @if($kits && count($kits) > 0)
            <label>Комплект:</label>
            <select name="kit" id="kit" class="form-control selectpicker" data-style="btn-secondary" data-live-search="true">
                <option selected disabled>Ничего не выбрано</option>
                @foreach($kits as $key => $kit)
                    <option data-original-name="{{ $kit->v_type }}" data-serial="{{ $kit->v_serial }}" data-target=".{{ md5($kit->v_serial) }}" value="{{ $kit->id }}">{{ $kit->v_type }} [{{ $kit->v_serial }}]</option>
                @endforeach
            </select>
            @if($direction == 'take')
                @foreach($kits as $key => $kit)
                    <div class="kit-equipment kit-equipment-take {{ md5($kit->v_serial) }}" style="display:none;">
                        <label>Оборудование этого комплекта:</label>
                        <ul>
                        @foreach($kit->equipment_list as $eq)
                            <li data-v_equipment_transfer="{{ $eq->v_equipment_transfer }}" >{{ $eq->model->v_name ?? 'Модель неизвестна' }} {{ $eq->model->v_vendor ?? 'Вендор неизвестен' }} [{{ $eq->v_equipment_number }}]</li>
                        @endforeach
                        </ul>
                    </div>
                @endforeach
            @endif
        @elseif($equipments && count($equipments) > 0)
            <label>Оборудование у клиента:</label>
            <select name="equipments" id="equipments" class="form-control">
                <option selected disabled>Ничего не выбрано</option>
                @foreach($equipments as $eq)
                    <option value="{{ $eq->v_equipment_number }}" data-serial="{{ $eq->v_equipment_number }}" data-name="{{ $eq->v_name }} {{ $eq->v_vendor }}">{{ $eq->v_name }} {{ $eq->v_vendor }} [{{ $eq->v_equipment_number }}]</option>
                @endforeach
            </select>
        @else
            <div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> {{ $direction == 'give' ? 'У вас нет оборудования' : 'У клиента нет оборудования' }}.</div>
        @endif
    </div>
    <div class="form-group kit-equipment"></div>
    <div class="form-group">
        <label>Монтажник:</label>
        <select name="installer" id="installer" class="form-control">
            @foreach($installers as $inst)
                <option {{ $selectedInstaller == $inst->id ? 'selected' : '' }} value="{{ $inst->id }}">{{ $inst->name }}</option>
            @endforeach
        </select>
    </div>
    @if($direction == 'give')
    <div class="form-group">
        <label for="v_kits_transfer">Тип передачи оборудования:</label>
        <select name="v_kits_transfer" id="v_kits_transfer" class="form-control">
            <option value="R">Аренда</option>
            <option value="S">Продажа</option>
            <option value="RS">Ответственное хранение</option>
        </select>
    </div>
    @elseif($direction == 'take' && $show_b_unbind_cntr)
    <div class="form-group">
        <label for="b_unbind_cntr">Оставить оборудование на контракте:</label>
        <select name="b_unbind_cntr" id="b_unbind_cntr" class="form-control">
            <option value="1">Отвязать от контракта</option>
            <option value="0">Отвязать от услуги, но оставить на контракте</option>
        </select>
    </div>
    @endif
</div>
<div class="modal-footer">
    @if($direction == 'give' && $kits && count($kits) > 0)
        <button type="button" class="btn btn-success" id="give_equipments">Внести</button>
    @elseif($direction == 'take' && (($equipments && count($equipments) > 0) || ($kits && count($kits) > 0)))
        <button type="button" class="btn btn-success" id="take_equipments">Забрать</button>
    @endif
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
</div>