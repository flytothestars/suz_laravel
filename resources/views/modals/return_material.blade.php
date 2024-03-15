<div class="modal-header">
    <h5 class="modal-title" id="returnModalLabel">Возврат расходников</h5>
    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
</div>
<form action="{{ route('return_material') }}" method="post" id="return_material">
    @csrf
    <input type="hidden" name="user_material_id" value="{{ $user_material->id }}">
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
        <div class="modal-materials">
        @if($material)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <th>Название</th>
                            <th>Тип</th>
                            <th>Количество</th>
                        </thead>
                        <tbody>
                            <tr>
                                <td><span class="h4 text-success">{{ $material->name }}</span></td>
                                <td><span class="h4">{{ $material->type }}</span></td>
                                <td><span class="h4">{{ $material->qty }} шт.</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            @else
                <span>Нет расходников. Это какая-то ошибка, срочно обратитесь к администраторам сайта.</span>
            @endif
        </div>
        <div class="form-group mt-3">
            <input type="number" min="1" step="1" name="qty" class="form-control" placeholder="Количество" max="{{ $material->qty }}" required>
        </div>
    </div>
    <div class="modal-footer">
    @if(\Auth::user()->getInstallerStocks()->count() > 0)
            <button type="submit" class="btn btn-success">Отправить на возврат</button>
        @endif
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
    </div>
</form>
