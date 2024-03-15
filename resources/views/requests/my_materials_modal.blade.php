<div class="modal-header">
    <h5 class="modal-title" id="materialsModalLabel">{{ $direction_material == 'give' ? 'Выдать абоненту' : 'Забрать у абонента' }}</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<div class="modal-body">
    <div class="form-group">
        <label>Монтажник:</label>
        <select name="installer_material" id="installer_material" class="form-control">
            @foreach($installers_material as $inst_m)
                <option {{ $selectedInstallerMaterial == $inst_m->id ? 'selected' : '' }} value="{{ $inst_m->id }}">{{ $inst_m->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="form-group">
        <label>Направление:</label>
        <select name="direction_material" id="direction_material" class="form-control">
            <option {{ $direction_material == 'give' ? 'selected' : '' }} value="give">Выдать абоненту</option>
            <option {{ $direction_material == 'take' ? 'selected' : '' }} value="take">Забрать у абонента</option>
        </select>
    </div>
    @if($direction_material == 'give')
    <div class="form-group my-materials">
        <select name="materials" class="form-control selectpicker" data-live-search="true" data-style="btn-secondary" data-size="5" data-mobile="true">
            @foreach($materials as $m)
                <option data-content="{{ $m->name }} (<span>{{ $m->qty }}</span> шт.)" data-qty="{{ $m->qty }}" data-limit_qty="{{ $m->limit_qty }}" value="{{ $m->material_id }}" data-name="{{ $m->name }}">{{ $m->name }}</option>
            @endforeach
        </select>
    </div>
    @elseif($direction_material == 'take')
    <div class="form-group client-materials">
        <select name="client_materials" class="form-control selectpicker" data-live-search="true" data-style="btn-secondary" data-size="5">
            @foreach($materials as $m)
                <option data-content="{{ $m->name }} (<span>{{ $m->qty }}</span> шт.)" data-qty="{{ $m->qty }}" value="{{ $m->id }}" data-name="{{ $m->name }}">{{ $m->name }}</option>
            @endforeach
        </select>
    </div>
    @endif
    <div class="form-group">
        <input type="number" name="qty" min="1" step="1" class="form-control" placeholder="Количество" max="{{ isset($materials[0]) ? $materials[0]->qty : '' }}">
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-success">Добавить</button>
    <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
</div>
<script>
    $(document).ready(function(){
        $('input[name="qty"]').on('keypress', function(event){
            var regex = new RegExp("^[0-9]+$");
            var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
            if (!regex.test(key)){
                event.preventDefault();
                return false;
            }
        });
    });
</script>