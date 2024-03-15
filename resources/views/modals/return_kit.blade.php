<div class="modal-header">
    <h5 class="modal-title" id="returnModalLabel">Возврат комплекта</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<form action="{{ route('return_kit') }}" method="post" id="return_kit">
    @csrf
    <input type="hidden" name="kit_id" value="{{ $kit->id }}">
    <div class="modal-body">
        <div class="form-group">
            <label for="stock">Выберите склад:</label>
            <select class="form-control" id="stock" name="stock" required>
                @if(\Auth::user()->getInstallerStocks()->count() > 0)
                    @foreach(\Auth::user()->getInstallerStocks() as $stock)
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
            @if($equipments && count($equipments) > 0)
                <div class="mb-4">
                    <div class="h4">Комплект:</div>
                    <div class="h3">{{ $kit->v_type }}</div>
                    <div class="my-2 h3 text-red">{{ $kit->v_serial }}</div>
                </div>
                <div class="h4">Список оборудования этого комплекта:</div>
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <th>Название</th>
                            <th>Серийный номер оборудования</th>
                        </thead>
                        @foreach($equipments as $eq)
                            <tr>
                                <td><span class="h4 text-success">{{ $eq->model->v_name ?? 'Модель неизвестна ' }} {{ $eq->model->v_vendor ?? 'Вендор неизвестен' }}</span></td>
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
        @if(\Auth::user()->getInstallerStocks()->count() > 0)
        <button type="submit" class="btn btn-success">Отправить на возврат</button>
        @endif
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
    </div>
</form>