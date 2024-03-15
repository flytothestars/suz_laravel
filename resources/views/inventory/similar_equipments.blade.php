@if($similarEquipments && count($similarEquipments) > 0)
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
                <th></th>
                <th>Серийный номер оборудования</th>
                <th>Серийный номер комплекта</th>
            </thead>
            @foreach($similarEquipments as $eq)
                <tr>
                    <td><input type="checkbox" name="equipments[]" value="{{ $eq->id }}"></td>
                    <td><span class="h4">{{ $eq->v_equipment_number }}</span></td>
                    <td><span class="h4 text-success">{{ $eq->kit->v_serial }}</span></td>
                </tr>
            @endforeach
        </table>
    </div>
@else
    <span>Нет экземпляров.</span>
@endif