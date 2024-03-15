@extends('layouts.app')

@php
    $pageTitle = 'Добавление расходных материалов';
    $selectedType = isset($_GET['type']) ? $_GET['type'] : '';
@endphp

@section('top-content')
    <a href="/materials" class="btn btn-white top-back-btn"><i class="fas fa-arrow-left"></i> Назад</a>
@endsection

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
            <div class="card-body pb-5">
                <div class="row">
                    <div class="col-md-4">
                        <form action="/materials/store" method="post">
                            @csrf
                            <div class="form-group">
                                <label for="name">Название:</label>
                                <input type="text" id="name" class="form-control" name="name" required autofocus>
                            </div>
                            <div class="form-group">
                                <label for="type">Тип:</label>
                                <select name="type" id="type" class="form-control" required>
                                    <option selected disabled>Не выбрано</option>
                                    @foreach($types as $type)
                                        <option value="{{ $type->id }}" {{ isset($_COOKIE['material_type']) && $type->id == $_COOKIE['material_type'] ? 'selected' : '' }}>{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button class="btn btn-success">Добавить</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-scripts')
    <script type="text/javascript" src="{{ asset('js/jquery.cookie.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            $('#type').on('change', function(){
                $.cookie('material_type', $(this).val(), { expires: 1, path: '/' });
            });
        });
    </script>
@endsection