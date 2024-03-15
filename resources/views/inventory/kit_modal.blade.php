<div class="text-center mb-4">
    <div class="h4">Это оборудование является частью комплекта:</div>
    <div class="my-3 h3 text-red">{{ $kit->v_serial }}</div>
</div>
<div class="h4 text-center">Список оборудования этого комплекта:</div>
<table class="table table-bordered">
    <thead>
        <th>Название</th>
        <th>Серийный номер</th>
    </thead>
    <tbody>
    @foreach($kit->equipments as $eq)
        <tr>
            <td>{{ $eq->model->v_name }} {{ $eq->model->v_vendor }}</td>
            <td>{{ $eq->v_equipment_number }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
<hr>