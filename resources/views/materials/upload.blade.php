@extends('layouts.app')

@php
    $pageTitle = 'Загрузка расходных материалов';
    $selectedType = isset($_GET['type']) ? $_GET['type'] : '';
@endphp

@section('top-content')
    <a href="/inventory" class="btn btn-white top-back-btn"><i class="fas fa-arrow-left"></i> Назад в склады</a>
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
                    <div class="col-md-3">
                        <form action="/materials/upload" method="post">
                            @csrf
                            <div class="form-group">
                                <label for="type">Тип:</label>
                                <select name="type" id="type" class="form-control selectpicker" required data-style="btn-secondary" data-live-search="true">
                                    <option selected disabled>Не выбрано</option>
                                    @foreach($types as $type)
                                        <option value="{{ $type->id }}" {{ $type->id == $selectedType ? 'selected' : '' }}>{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if($materials)
                            <div class="form-group">
                                <label for="material">Название:</label>
                                <select name="material" id="material" class="form-control selectpicker" data-style="btn-secondary" data-live-search="true" required>
                                    <option selected disabled>Не выбрано</option>
                                    @foreach($materials as $m)
                                        <option value="{{ $m->id }}">{{ $m->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            @if($stocks)
                            <div class="form-group">
                                <label for="stock">Склад:</label>
                                <select name="stock" id="stock" class="form-control selectpicker" data-style="btn-secondary" data-live-search="true" required>
                                    <option selected disabled>Не выбрано</option>
                                    @foreach($stocks as $stock)
                                        <option value="{{ $stock->id }}">{{ $stock->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            <div class="form-group">
                                <label for="qty">Количество</label>
                                <input type="number" min="1" step="1" class="form-control" name="qty" placeholder="Введите значение">
                            </div>
                            <button class="btn btn-success btn-lg px-5">Добавить</button>
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
            $('#type').on('changed.bs.select', function(){
                let type = $(this).val();
                window.location.href = $.query.SET('type', type);
            });
        });
    </script>
@endsection