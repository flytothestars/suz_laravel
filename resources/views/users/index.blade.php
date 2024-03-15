@extends('layouts.app')

@section('pageTitle', 'Пользователи')

@section('styles')
<link rel="stylesheet" href="{{ asset('css/jquery-ui.min.css') }}">
@endsection

@php
    $department_id = isset($_GET['department_id']) ? $_GET['department_id'] : null;
    $location_id = isset($_GET['location_id']) ? $_GET['location_id'] : null;
    $current_date = isset($_GET['date']) ? $_GET['date'] : date("Y-m-d");
    $selected_role = isset($_GET['role']) ? $_GET['role'] : null;
@endphp

@section('content')
    <div class="container-fluid mt--7" id="main_container">
        <div class="card min-height-500 mb-4">
            <div class="card-body">
                @if(auth()->user()->hasRole('супервизер') && !auth()->user()->hasAnyRole(['администратор', 'диспетчер', 'кладовщик']))
                <h1>Техники</h1>
                @else
                <h1>Пользователи</h1>
                @endif
                <div class="row mb-4">
                    <div class="col-md-3 col-lg-2">
                        <label>Филиал:</label>
                        @if(auth()->user()->hasRole('супервизер') && !auth()->user()->hasAnyRole(['администратор', 'диспетчер', 'кладовщик']))
                        <select id="department_id" class="form-control">
                            <option value="{{ $departments->v_ext_ident }}">{{ $departments->name }}</option>
                        </select>
                        @else
                        <select id="department_id" class="form-control">
                            <option selected value='all'>Все филиалы</option>
                            @foreach($departments as $department)
                                <option {{ $department->id_department == $department_id ? 'selected' : '' }} value="{{ $department->id_department }}">{{ $department->v_name }}</option>
                            @endforeach
                        </select>
                        @endif
                    </div>
                    @if($locations)
                    <div class="col-md-3 col-lg-2">
                        <label>Участок:</label>
                        @if(auth()->user()->hasRole('супервизер') && !auth()->user()->hasAnyRole(['администратор', 'диспетчер', 'кладовщик']))
                        <select id="location_id" class="form-control">
                            <option value="{{ $locations->id }}">{{ $locations->v_name }}</option>
                        </select>
                        @else
                        <select id="location_id" class="form-control">
                            <option selected disabled>Не выбрано</option>
                            @foreach($locations as $location)
                                <option {{ $location->id == $location_id ? 'selected' : '' }} value="{{ $location->id }}">{{ $location->v_name }}</option>
                            @endforeach
                        </select>
                        @endif
                    </div>
                    @endif
                    @if(!auth()->user()->hasRole('супервизер'))
                    <div class="col-md-3 col-lg-2">
                        <label>Роль:</label>
                        <select id="role" class="form-control">
                            <option selected value='all'>Все</option>
                            @foreach($roles as $role)
                                <option {{ $role->id == $selected_role ? 'selected' : '' }} value="{{ $role->id }}">{{ $role->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Поиск пользователя</label>
                        <input type="text" class="form-control" id="search_users">
                    </div>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <th>ID</th>
                            <th>Имя</th>
                            <th>Телефон</th>
                            <th>Email</th>
                            <th>Участок</th>
                            <th>Роли</th>
                            <th></th>
                        </thead>
                        <tbody>
                            @foreach($users as $user)
                                <tr class="user-row">
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td><a href="tel:{{ str_replace(' ', '', $user->phone) }}">{{ $user->phone }}</a></td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                    	@if($user->locations_text != '')
                                    	<button class="btn btn-white btn-sm" style="cursor:default;" title="{!! $user->locations_text !!}" data-html="true" data-toggle="tooltip" data-placement="bottom"><i class="fas fa-list-ul"></i></button>
                                    	@else
										<span class="text-gray">Не привязан</span>
                                    	@endif
                                    <td>{!! $user->roles_html !!}</td>
                                    <td>
                                        <a href="/users/{{ $user->id }}" class="btn btn-success">Посмотреть</a>
                                        @if(Auth::user()->hasRole('администратор'))
                                        <a href="/auth_by_id/{{ $user->id }}" class="btn btn-primary">Авторизоваться</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="my-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-scripts')
    <script type="text/javascript">
        $(document).ready(function(){
            $('.user-row').on('click', function(){
                $(this).find('a').trigger('click');
            });
            $('#department_id').on('change', function(){
                let department_id = $(this).val();
                window.location.href = $.query.SET('department_id', department_id).REMOVE('location_id');
            });
            $('#location_id').on('change', function(){
                let location_id = $(this).val();
                window.location.href = $.query.SET('location_id', location_id);
            });
            $('#role').on('change', function(){
                let role_id = $(this).val();
                window.location.href = $.query.SET('role', role_id);
            });
        });
        $("#search_users").autocomplete({
            source: function(request, response){
                $.ajax({
                    url: "/search_users",
                    dataType: "json",
                    data: {
                        q: request.term
                    },
                    success: function(data){
                        response($.map(data, function(item){
                            return {
                                label: item.name,
                                value: item.id
                            };
                        }));
                    }
                });
            },
            minLength: 3,
            select: function(event, ui){
                event.preventDefault();
                window.location.href = window.location.origin + '/users/' + ui.item.value;
            },
        });
    </script>
@endsection