@if($stocks->count() > 0)
	<option value="0">Не выбрано</option>
	@foreach($stocks as $stock)
	    <option value="{{ $stock->id }}">{{ $stock->name }}</option>
	@endforeach
@else
	<option disabled selected>Нет</option>
@endif