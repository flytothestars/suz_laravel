@extends('layouts.app')

@section('pageTitle', 'Поиск заявки')

@section('content')
<div class="container-fluid mt--7">
    <div class="card min-height-500">
        <div class="card-body">
            <h1>Результат по запросу <code>"{{ request()->q }}"</code></h1>
            <div class="row">
                <div class="col-md-12">
                    @if($kits && $kits->count() > 0)
	                    <div class="table-responsive">
	                        <table class='table table-bordered'>
	                            <thead>
	                                <th>#</th>
	                                <th>Мнемоника</th>
	                                <th>Серийный номер</th>
	                                <th>Филиал</th>
	                                <th>Дата активации</th>
	                                <th>Владелец</th>
	                                <th>Возвращено?</th>
	                                <th>Комплектующие</th>
	                                <th></th>
	                            </thead>
	                            <tbody>
	                                @foreach($kits as $kit)
	                                <tr>
	                                    <td>{{ $kit->id }}</td>
	                                    <td>{{ $kit->v_type }}</td>
	                                    <td>{{ $kit->v_serial }}</td>
	                                    <td>{{ $kit->v_department }}</td>
	                                    <td>{{ $kit->dt_activate }}</td>
	                                    <td>{{ $kit->owner }}</td>
	                                    <td>{{ $kit->returned }}</td>
	                                    <td>
	                                    	@foreach($kit->equipments as $eq)
	                                    		<div class="text-success mb-2 font-weight-bold" style="display:block;font-size:10pt;">
	                                    			{{ $eq->model->v_vendor }} {{ $eq->model->v_name }}
	                                    			<div class="badge badge-danger" style="font-size:10pt;">
	                                    				{{ $eq->v_equipment_number }}
	                                    			</div>
	                                    		</div>
						                    @endforeach
	                                    </td>
	                                    <td>
	                                    	<a href="/kit/{{ $kit->id }}" class="btn btn-secondary btn-sm">Посмотреть историю</a>
	                                    </td>
	                                </tr>
	                                @endforeach
	                            </tbody>
	                        </table>
	                    </div>
                    @endif
                </div>
                @if($suzRequests && $suzRequests->count() > 0)
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class='table table-hover'>
                                <thead>
                                    <th>ID</th>
                                    <th>Наряд</th>
                                    <th>Тип заказа</th>
                                    <th>Статус</th>
                                    <th>Дата работ</th>
                                    <th>Дата получения</th>
                                    <th></th>
                                </thead>
                                <tbody>
                                    @foreach($suzRequests as $req)
                                    <tr class="request-row" onclick="window.location.href='requests/{{ $req->id }}'">
                                        <td>{{ $req->id }}</td>
                                        <td>{{ $req->id_flow }}</td>
                                        <td><strong>{{ $req->kind_works }}</strong></td>
                                        <td class="text-{{ getStatusClass($req->status) }}"><strong>{{ $req->status }}</strong></td>
                                        <td class="text-warning"><strong>{{ date("Y-m-d", strtotime($req->dt_plan_date)) }}</strong></td>
                                        <td>{{ date("d.m.Y H:i", strtotime($req->dt_start)) }}</td>
                                        <td><a href="requests/{{ $req->id }}" class="btn btn-success">Посмотреть</a></td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection