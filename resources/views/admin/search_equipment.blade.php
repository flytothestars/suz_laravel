<table class="table mt-5">
	<thead>
		<th>#</th>
		<th>ID_FLOW</th>
		<th>V_TYPE</th>
		<th>V_SERIAL</th>
		<th>V_DEPARTMENT</th>
		<th>Владелец</th>
		<th>Склад</th>
		<th></th>
	</thead>
	<tbody>
		@foreach($kits as $kit)
		<tr>
			<td>{{ $kit->id }}</td>
			<td>{{ $kit->id_flow }}</td>
			<td>{{ $kit->v_type }}</td>
			<td><span class="text-danger font-weight-bold">{{ $kit->v_serial }}</span></td>
			<td>{{ $kit->v_department }}</td>
			<td>
				@if($kit->owner_id)
					@if($kit->owner)
						{{ $kit->owner->name }}
					@else
						{{ $kit->owner_id }}
					@endif
				@endif
			</td>
			<td>
				@if($kit->stock_id)
					{{ $kit->stock->name }}
				@endif
			</td>
			<td>
				<button class="btn btn-success move-btn" data-id="{{ $kit->id }}">Сменить владельца</button>
				<button class="btn btn-primary stock-btn" data-id="{{ $kit->id }}">Сменить склад</button>
				@if($kits->count() > 1 && auth()->user()->hasRole('администратор'))
				<button class="btn btn-danger delete-btn" data-id="{{ $kit->id }}">Удалить</button>
				@endif
			</td>
		</tr>
		@endforeach
	</tbody>
</table>
