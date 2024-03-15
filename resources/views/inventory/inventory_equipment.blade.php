@if($equipments && $equipments->count() > 0)
<h4 class="text-center">{{ $equipments[0]->name ?? '' }}</h4>
<div class="table-responsive">
    <table class="table table-bordered">
        <thead>
            <th>Серийный номер комплекта</th>
            <th>Дата активации комплекта</th>
            <th>Серийный номер оборудования</th>
            <th>Название модели</th>
            <th>Вендор</th>
        </thead>
        @foreach($equipments as $eq)
            <tr>
                <td>{{ $eq->kit->v_serial ?? 'Серийный номер комплекта неизвестна' }}</td>
                <td>{{ $eq->kit->dt_activate ?? 'Дата активации неизвестна' }}</td>
                <td>{{ $eq->v_equipment_number ?? 'Серийный номер оборудования неизвестен' }}</td>
                <td>{{ $eq->model->v_name ?? 'Модель неизвестна' }}</td>
                <td>{{ $eq->model->v_vendor ?? 'Вендор неизвестен' }}</td>
            </tr>
        @endforeach
    </table>
</div>
@else
<div class="alert alert-warning"><i class="fas fa-exclamation-triangle"></i> Нет оборудования.</div>
@endif