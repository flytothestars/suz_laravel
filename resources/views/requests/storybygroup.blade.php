@extends('layouts.app')

@section('pageTitle', 'Сводка')

@php
    $selected_status_id = isset($_GET['status']) ? $_GET['status'] : 0;
    $selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
    $selected_department = isset($_GET['department']) ? $_GET['department'] : '';
    $selected_work_group = isset($_GET['work_group']) ? $_GET['work_group'] : '';
@endphp

@section('content')
<div class="container-fluid mt--7">
    <div class="card min-height-500 mb-5">
        <div class="card-body">
            <h1>Заявки по группам</h1>
            <form action="" style="display: block; width: 100%;">
                <div class="row" id="filters_row">
                    <div class="col-md-2">
                        <label for="filter_department">Филиал:</label>
                        <select name="department" class="form-control" id="filter_department">
                            <option value="all">Выберите филиал</option>
                            @foreach($departments as $department)
                            <option value="{{ $department->v_ext_ident }}" {{ ($department->v_ext_ident == $selected_department) ? 'selected' : '' }}>{{ $department->v_name }}</option>
                            @endforeach
                        </select>
                    </div>
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
                        <label for="filter_group">Группа работ:</label>
                        <select name="work_group" class="form-control" id="filter_group">
                            <option value="all">Все</option>
                            <option value="new" {{ ($selected_work_group == 'new') ? 'selected' : '' }}>Новые</option>
                            <option value="service" {{ ($selected_work_group == 'service') ? 'selected' : '' }}>Сервис</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label for="filter_date">Дата:</label>
                        <div class="form-group">
                            <input class="form-control datepicker" id="filter_date" name="date" placeholder="Date" type="text" value="{{ $selected_date }}">
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label>&nbsp;</label>
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
                        <tr class="request-row" onclick="window.location.href='requests/{{ $req->request_id }}'">
                            <td>{{ $req->request_id }}</td>
                            <td>{{ $req->id_flow }}</td>
                            <td><strong>{{ $req->kind_works }}</strong></td>
                            <td>{{ $req->type_works }}</td>
                            <td class="text-{{ getStatusClass($req->status) }}"><strong>{{ $req->status }}</strong></td>
                            <td class="text-warning"><strong>{{ date("Y-m-d", strtotime($req->dt_plan_date)) }}</strong></td>
                            <td>{{ date("d.m.Y", strtotime($req->dt_flow_dt_event)) }}</td>
                            <td><a href="requests/{{ $req->request_id }}" target="_blank" class="btn btn-success">Посмотреть</a></td>
                        </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="8" class="text-center"><span class="text-gray">По выбранным фильтрам заявок нет.</span></td>
                        </tr>
                    @endif
                    </tbody>
                </table>
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