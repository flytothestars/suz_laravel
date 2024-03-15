@foreach($instances as $item)
    <option value="{{ $item->id }}">{{ $item->v_equipment_number }}</option>
@endforeach