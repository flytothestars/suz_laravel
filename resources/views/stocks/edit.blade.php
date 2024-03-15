@extends('layouts.app')

@php
    $pageTitle = 'Редактировать склад';
    $selectedDepartment = isset($_GET['department']) ? $_GET['department'] : '';
    $selectedLocation = isset($_GET['location']) ? $_GET['location'] : '';
@endphp

@section('pageTitle', $pageTitle)

@section('content')
    <div class="container-fluid mt--7">
        <div class="card min-height-500">
            <div class="card-header">
                @if(session('message'))
                    <div class="my-4 alert alert-{{ session('success') ? 'success' : 'danger' }} alert-temporary">{{ session('message') }}</div>
                @endif
                <h1>{{ $pageTitle }}</h1>
            </div>
            <div class="card-body">
                <div class="row py-3">
                    <div class="col-md-5 col-lg-4">
                        <form action="/stocks/{{ $stock->id }}/update" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="name">Название: *</label>
                                <input type="text" class="form-control" name="name" required placeholder="Введите название склада" value="{{ $stock->name }}">
                            </div>
                            <div class="form-group">
                                <label for="department_id">Филиал: *</label>
                                <select name="department_id" class="form-control" id="department_id" required>
                                    @foreach($departments as $department)
                                    <option {{ $department->id_department == $selectedDepartment || $department->id_department == $stock->department_id ? 'selected' : '' }} value="{{ $department->id_department }}">{{ $department->v_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="location_id">Участок: *</label>
                                <select name="location_id" class="form-control" id="location_id" required>
                                    @foreach($locations as $loc)
                                    <option value="{{ $loc->id }}" {{ $loc->id == $selectedLocation || $loc->id == $stock->location_id ? 'selected' : '' }}>{{ $loc->v_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="users">Ответственные: *</label>
                                <select name="users[]" class="form-control selectpicker" required data-style="btn-secondary" data-live-search="true" multiple id="users">
                                    @foreach($users as $user)
                                    <option value="{{ $user->id }}" {{ $stock->users->contains('id', $user->id) ? 'selected' : '' }}>{{ $user->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="parent_id">Родительский склад:</label>
                                <select name="parent_id" class="form-control" id="parent_id">
                                    <option disabled selected>Нет</option>
                                    @foreach($parent_stocks as $parent_stock)
                                    <option value="{{ $parent_stock->id }}" {{ $stock->parent_id == $parent_stock->id ? 'selected' : '' }}>{{ $parent_stock->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <input type="checkbox" name="return" id="return" {{ $stock->return ? 'checked' : '' }}>
                                <label for="return">Склад возврата</label>
                            </div>
                            <button class="btn btn-success">Сохранить изменения</button>
                            <a href="/stocks" class="btn btn-secondary">Отмена</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-scripts')
    <script type="text/javascript">
        $(document).ready(function(){
            $("#department_id").on("change", function(){
                let department_id = $(this).val();
                $.ajax({
                    url: '/getLocationsOptionsByDepartment',
                    data: {
                        department_id: department_id
                    },
                    success(response){
                        $("#location_id").html(response['html']);
                    }
                });
                $.ajax({
                    url: '/getParentStocksOptionsByDepartment',
                    data: {
                        department_id: department_id
                    },
                    success(response){
                        $('#parent_id').html(response['html']);
                    }
                });
            });
            $("#location_id").on("change", function(){
                let department_id = $("#department_id").val();
                let location_id = $(this).val();
                $.ajax({
                    url: '/getStockholdersByLocation',
                    data: {
                        department_id: department_id,
                        location_id: location_id
                    },
                    success(response){
                        $("#users").html(response['html']).selectpicker('refresh');
                    }
                });
            });
        });
    </script>
@endsection