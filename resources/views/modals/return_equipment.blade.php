<div class="modal-header">
    <h5 class="modal-title" id="returnModalLabel">Возврат оборудования</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<form action="{{ route('return_equipment') }}" method="post" id="return_equipment">
    @csrf
    <div class="modal-body">
        <div class="form-group">
            <label for="stock">Выберите склад:</label>
            <select class="form-control" id="stock" name="stock" required>
                @if(\Auth::user()->stocks->count() > 0)
                    @foreach(\Auth::user()->stocks as $stock)
                        @if($stock->return == 0)
                            <option value="{{ $stock->id }}">{{ $stock->name }}</option>
                        @endif
                    @endforeach
                @else
                    <option selected disabled>Вы не привязаны к складам</option>
                @endif
            </select>
        </div>
        <div class="modal-equipments">
            @if($similarEquipments && count($similarEquipments) > 0)
                <div class="h4">Список экземпляров:</div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <th><input type="checkbox" class="select-all-equipments-to-return"></th>
                            <th>Название</th>
                            <th>Серийный номер оборудования</th>
                        </thead>
                        @foreach($similarEquipments as $eq)
                            <tr>
                                <td><input type="checkbox" name="return_equipments[]" value="{{ $eq->id }}"></td>
                                <td><span class="h4 text-success">{{ $eq->model->v_name }} {{ $eq->model->v_vendor }}</span></td>
                                <td><span class="h4">{{ $eq->v_equipment_number }}</span></td>
                            </tr>
                        @endforeach
                    </table>
                </div>
            @else
                <span>Нет экземпляров.</span>
            @endif
        </div>
    </div>
    <div class="modal-footer">
        @if(\Auth::user()->stocks->count() > 0)
        <button type="submit" class="btn btn-success">Отправить на возврат</button>
        @endif
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
    </div>
</form>