@extends('layouts.app')

@php
    $pageTitle = 'Склады';
    $selectedDepartment = isset($_GET['department']) ? $_GET['department'] : '';
    $selectedLocation = isset($_GET['location']) ? $_GET['location'] : '';
@endphp

@section('top-content')
    <a href="/settings" class="btn btn-white top-back-btn"><i class="fas fa-arrow-left"></i> Вернуться в настройки</a>
@endsection

@section('pageTitle', $pageTitle)

@section('content')
    <div class="container-fluid mt--7">
        <div class="card min-height-500">
            <div class="card-header">
                <div class="row">
                    @if(session('message'))
                        <div class="col-md-12">
                            <div class="my-4 alert alert-{{ session('success') ? 'success' : 'danger' }} alert-temporary">{{ session('message') }}</div>
                        </div>
                    @endif
                    <div class="col-md-4">
                        <h1>{{ $pageTitle }}</h1>
                    </div>
                    <div class="col-md-8 text-right">
                        <a href="/stocks/create" class="btn btn-primary">Создать склад</a>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row py-3">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <th>ID</th>
                                    <th>Название</th>
                                    <th>Филиал</th>
                                    <th></th>
                                </thead>
                                <tbody>
                                    @if($stocks && $stocks->count() > 0)
                                        @foreach($stocks as $stock)
                                        <tr>
                                            <td>{{ $stock->id }}</td>
                                            <td>{{ $stock->name }}</td>
                                            <td>{{ $stock->department->name }}</td>
                                            <td>
                                                <a href="/stocks/{{ $stock->id }}/edit" class="btn btn-primary"><i class="fas fa-pen"></i></a>
                                                <button class="btn btn-danger delete-btn" data-id="{{ $stock->id }}"><i class="fas fa-trash"></i></button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    @else
                                        <tr>
                                            <td colspan='3' class='text-center'>Нет складов.</td>
                                        </tr>
                                    @endif
                                </tbody>
                                <div class="my-2">
                                    {{ $stocks->links() }}
                                </div>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @csrf
@endsection

@section('page-scripts')
    <script type="text/javascript">
        $(document).ready(function(){
            $(".delete-btn").on("click", function(){
                let id = $(this).data('id');
                if(confirm('Вы действительно хотите удалить склад?')){
                    $.ajax({
                        url: '/stocks/delete',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            _token: $("input[name='_token']").val(),
                            id: id
                        },
                        success(response){
                            alert(response['message']);
                            if(response['success']){
                                window.location.reload();
                            }
                        }
                    });
                }
            });
        });
    </script>
@endsection