@if($locations->count() > 0)
	<option selected disabled>Не выбрано</option>
	@foreach($locations as $loc)
	    <option value="{{ $loc->id }}" data-id_location="{{ $loc->id_location }}">{{ $loc->v_name }}</option>
	@endforeach
@else
<option value="0">Не выбрано</option>
@endif