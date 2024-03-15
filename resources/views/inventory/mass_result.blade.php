<div class="table-responsive">
	<table class="table table-bordered">
		<thead>
			<th>Серийный номер</th>
			<th>Комплект</th>
			<th>Местонахождение</th>
		</thead>
		<tbody>
			@foreach($kits as $key => $kit)
				@if(!$kit->wrong)
				<tr>
					<td>{{ $kit->v_serial }}</td>
					<td>{{ $kit->v_type }}</td>
					<td>{{ $kit->stock->name }}</td>
				</tr>
				@else
					<tr class="alert-danger" title="Данное оборудование не может быть выдано или перемещено.">
						<td>{{ $key }}</td>
						<td>{{ $kit->v_type }}</td>
						<td>
							@if(isset($kit->stock->name))
								{{ $kit->stock->name }}
							@else
								{{ $kit->owner }}
							@endif
						</td>
					</tr>
				@endif
			@endforeach
		</tbody>
	</table>
</div>
<input type="hidden" id="mass_modal_clean" value="{{ $clean }}">
<input type="hidden" id="mass_modal_kit_ids" value="{{ implode(',', $kit_ids) }}">