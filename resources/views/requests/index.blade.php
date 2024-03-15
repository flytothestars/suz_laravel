@extends('layouts.app')

@section('pageTitle', 'Сводка')

@php
    $selected_status_id = isset($_GET['status']) ? $_GET['status'] : 0;
    $selected_date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-d', strtotime('now -1 week'));
    $selected_date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');
    $selected_department = isset($_GET['department']) ? $_GET['department'] : '';
    $selected_kind_work_id = isset($_GET['work_type']) ? $_GET['work_type'] : '';
    $selected_type_work_id = isset($_GET['work_subtype']) ? $_GET['work_subtype'] : '';
@endphp

@section('content')
<div class="container-fluid mt--7">
    <div class="card min-height-500 mb-5">
        <div class="card-body">
            <h1>Все заявки</h1>
            <form action="" style="display: block; width: 100%;">
                <div class="row" id="filters_row">
                    <div class="col-md-2">
                        <label for="filter_status">Статус:</label>
                        <select name="status" class="form-control" id="filter_status">
                            <option value="all">Все</option>
                            @foreach($statuses as $status)
                            <option value="{{ $status->id }}" {{ ($status->id == $selected_status_id) ? 'selected' : '' }}>{{ $status->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="filter_department">Филиал:</label>
                        @if(auth()->user()->hasRole('инспектор') && !auth()->user()->hasAnyRole('администратор', 'диспетчер'))
                        <select name="department" class="form-control">
                            @foreach($inspector_deps as $inspector_dep)
                            <option value="{{ $inspector_dep['v_ext_ident'] }}" 
                            {{ ($inspector_dep["v_ext_ident"] == $selected_department) ? 'selected' : '' }}>{{ $inspector_dep['v_name'] }}</option>
                            @endforeach
                        </select>
                        @else
                        <select name="department" class="form-control" id="filter_department">
                            <option value="all">Все</option>
                            @foreach($departments as $department)
                            <option value="{{ $department->v_ext_ident }}" {{ ($department->v_ext_ident == $selected_department) ? 'selected' : '' }}>{{ $department->v_name }}</option>
                            @endforeach
                        </select>
                        @endif
                    </div>
                    <div class="col-md-2">
                        <label for="filter_type">Тип работ:</label>
                        <select name="work_type" class="form-control" id="filter_type">
                            <option value="all">Все</option>
                            @foreach($kind_works as $kind)
                            <option value="{{ $kind->id_kind_work_inst }}" {{ ($kind->id_kind_work_inst == $selected_kind_work_id) ? 'selected' : '' }}>{{ $kind->v_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @if($type_works)
                    <div class="col-md-2">
                        <label for="filter_subtype">Подтип работ:</label>
                        <select name="work_subtype" class="form-control" id="filter_subtype">
                            <option value="all">Все</option>
                            @foreach($type_works as $type)
                            <option value="{{ $type->id_type_work }}" {{ ($type->id_type_work == $selected_type_work_id) ? 'selected' : '' }}>{{ $type->v_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    @endif
                    <div class="col-md-2">
                        <label for="filter_date_from">Дата получения:</label>
                        <div class="form-group">
                            <input class="form-control datepicker" id="filter_date_from" name="date_from" placeholder="Start date" type="text" value="{{ $selected_date_from }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label for="filter_date_to">&nbsp;</label>
                        <div class="form-group">
                            <input class="form-control datepicker" id="filter_date_to" name="date_to" placeholder="End date" type="text" value="{{ $selected_date_to }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        @if(!$type_works)
                        <label>&nbsp;</label>
                        @endif
                        <button id="apply_filters" type="submit" class="show btn btn-primary">Применить</button>
                    </div>
                </div>
            </form><br>
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <th>#</th>
                        <th>Наряд</th>
                        <th>Тип работ</th>
                        <th>Подтип работ</th>
                        <th>Статус</th>
                        <th>Дата работ</th>
                        <th>Дата получения</th>
                        <th></th>
                    </thead>
                    <tbody>
                    @if($requests && count($requests) > 0)
                        @foreach($requests as $req)
                        @if($req->kind_works == 'Демонтаж' && date("Y-m-d", strtotime($req->dt_plan_date)) <= date("Y-m-d", strtotime('-6 day')) && $req->status != 'Выполнено')
                        <tr style="color: #a72828;" class="request-row" onclick="window.location.href='requests/{{ $req->id }}'">
                            <td>{{ $req->id }}</td>
                            <td>{{ $req->id_flow }}</td>
                            <td><strong>{{ $req->kind_works }}</strong></td>
                            <td>{{ $req->type_works }}</td>
                            <td class="text-{{ getStatusClass($req->status) }}"><strong>{{ $req->status }}</strong></td>
                            <td class="text-warning"><strong>{{ date("Y-m-d", strtotime($req->dt_plan_date)) }}</strong></td>
                            <td>{{ date("d.m.Y H:i", strtotime($req->dt_start)) }}</td>
                            <td><a href="requests/{{ $req->id }}" class="btn btn-success">Посмотреть</a></td>
                        </tr>
                        @else
                        <tr class="request-row" onclick="window.location.href='requests/{{ $req->id }}'">
                            <td>{{ $req->id }}</td>
                            <td>{{ $req->id_flow }}</td>
                            <td><strong>{{ $req->kind_works }}</strong></td>
                            <td>{{ $req->type_works }}</td>
                            <td class="text-{{ getStatusClass($req->status) }}"><strong>{{ $req->status }}</strong></td>
                            <td class="text-warning"><strong>{{ date("Y-m-d", strtotime($req->dt_plan_date)) }}</strong></td>
                            <td>{{ date("d.m.Y H:i", strtotime($req->dt_start)) }}</td>
                            <td><a href="requests/{{ $req->id }}" class="btn btn-success">Посмотреть</a></td>
                        </tr>
                        @endif
                        @endforeach
                    @else
                        <tr>
                            <td colspan="8" class="text-center"><span class="text-gray">По выбранным фильтрам заявок нет.</span></td>
                        </tr>
                    @endif
                    </tbody>
                </table>
                @if($requests && count($requests) > 0)
                    {{ $requests->appends(request()->query())->links() }}
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-scripts')
<script type="text/javascript">
    $(document).ready(function(){
    });
</script>
@endsection