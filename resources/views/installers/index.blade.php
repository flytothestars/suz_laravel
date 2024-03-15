@extends('layouts.app')

@section('pageTitle', 'Маршрутный лист')

@section('top-content')
<!--     <div class="row mb-4">
        <div class="col-md-12">
            <button class="btn btn-secondary float-right"><i class="fas fa-print"></i> Печать</button>
        </div>
    </div> -->
@endsection

@section('styles')
@endsection

@php
    $current_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
@endphp

@section('content')
    <div class="container-fluid mt--7">
        <div class="card min-height-500">
            <div class="card-body">
                <h1>Маршрутный лист{{ $routeList ? " #" . $routeList->id : '' }}</h1>
                <input type="hidden" id="file_name" value="Маршрутный лист{{ $routeList ? " #" . $routeList->id : '' }}">
                <button class="btn btn-primary float-right" id="save"><i class="far fa-file-excel"></i> Скачать</button>
                <div class="row my-3">
                    <div class="col-md-12">
                        @if(\Auth::user()->locations->count() > 0)
                            <div class="h5 font-weight-400">
                                <i class="fas fa-map-marker-alt"></i> 
                                @foreach(\Auth::user()->locations as $key => $loc)
                                    {{ $loc->v_name }}
                                @endforeach
                            </div>
                        @else
                        <h5 class="font-weight-400">Не привязан к участку</h5>
                        @endif
                    </div>
                </div>
                <div class="row mb-4">
                    <div class="col-md-3">
                        <label class="d-block text-uppercase font-weight-bold mb-3">Дата:</label>
                        <input type="text" id="routelist_date" class="form-control datepicker" value="{{ $current_date }}">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            @if(session('message'))
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="alert alert-{{ session('message') == 'Заявка завершена успешно.' || session('message') == 'Регистрация прошла успешно.' ? 'success' : 'danger' }} alert-temporary">{{ session('message') }}</div>
                                </div>
                            </div>
                            @endif
                            @if($errors->any())
                                <div class="alert alert-danger">
                                    <h5 class="text-white">Ошибки:</h5>
                                    <ul class="m-0">
                                        @foreach($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <table class="table table-hover" id="routelist_table">
                                <thead>
                                    <th>#</th>
                                    <th>Наряд</th>
                                    <th>Тип работ</th>
                                    <th>Услуги</th>
                                    <th>Номер договора</th>
                                    <th>ФИО абонента</th>
                                    <th>Время работ</th>
                                    <th>Статус</th>
                                    <th>Адрес</th>
                                    <th>Дата получения</th>
                                    <th></th>
                                </thead>
                                <tbody>
                                @if($requests)
                                <form method="post" action="{{action('SuzRequestController@downloadWord')}}" class="form">
                                <input type="hidden"  name="_token" value="{{ csrf_token() }}">
                                @foreach($requests as $req)
                                <input type="hidden" name="ids[]" value="{{ $ids[] = $req->id }}">
                                    <tr class="request-row" onclick="window.location.href='requests/{{ $req->id }}'">
                                        <td>{{ $req->id }}</td>
                                        <td>{{ $req->id_flow }}</td>
                                        <td><strong>{{ $req->kind_works }}</strong></td>
                                        <td>{!! $req->services !!}</td>
                                        <td>{{ $req->v_contract }}</td>
                                        <td>{{ $req->v_client_title }}</td>
                                        <td class="text-warning" title="{{ $req->dt_plan_date }}"><strong class="h3 font-weight-bold">{{ date("H:i", strtotime($req->dt_plan_date)) }}</strong></td>
                                        <td class="text-{{ getStatusClass($req->status) }}"><strong>{{ $req->status }}</strong></td>
                                        <td>{{ $req->address }}</td>
                                        <td>{{ date("d.m.Y H:i", strtotime($req->dt_start)) }}</td>
                                        <td><a href="requests/{{ $req->id }}" class="btn btn-success">Посмотреть</a></td>
                                    </tr>
                                @endforeach
                                <button type="submit" class="btn btn-primary float-left my-3" id="word_all"><i class="far fa-file-word"></i> Распечатать все заявки</button>
                                </form>
                                @else
                                    <tr class="text-center">
                                        <td colspan="8"><span class="text-gray h3">Нет маршрутных листов на выбранную дату.</span></td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-scripts')
    <script type="text/javascript" src="{{ asset('js/tableExport.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            $('#routelist_date').on('change', function(){
                let date = $(this).val();
                window.location.href = $.query.SET('date', date);
            });
            $("#save").on("click", function(){
                $("#routelist_table").tableExport({
                    type: 'excel',
                    escape: 'false',
                    htmlContent: 'true',
                    fileName: $("#file_name").val(),
                    ignoreColumn: [9]
                });
            });
            $(document).on('submit', 'form', function() {
		        $('#word_all').attr('disabled', 'disabled');
		    });
        });
    </script>
@endsection