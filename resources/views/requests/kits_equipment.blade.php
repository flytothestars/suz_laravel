<label>Оборудование этого комплекта:</label>
<ul>
@foreach($equipments as $eq)
	<li>{{ $eq->model->v_name ?? 'Модель неизвестна' }} {{ $eq->model->v_vendor ?? 'Вендор неизвестен' }} [{{ $eq->v_equipment_number }}]</li>
@endforeach
</ul>