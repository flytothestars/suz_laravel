@extends('layouts.app')

@section('pageTitle', 'История оборудования ' . $kit->v_serial)

@section('top-content')
    <a href="{{ url()->previous() != url()->current() ? url()->previous() : '/inventory' }}" class="btn btn-secondary top-back-btn"><i class="fas fa-arrow-left"></i> Назад</a>
@endsection

@section('styles')
    <style>
        th{
            font-weight: bolder!important;
        }
    </style>
@endsection

@section('content')
    <div class="container-fluid mt--7">
        <div class="card min-height-500">
            <div class="card-body">
                <h1 class="display-3">Комплект <b>{{ $kit->v_serial }}</b></h1>
                <h5 class="mb-4">Текущее расположение:
                    @if($kit->stock)
                        <b>{{ $kit->stock->name }}</b>
                    @else
                        {{ isset($kit->owner->name) ? $kit->owner->name : $kit->owner }}
                    @endif</h5>
                <div class="row">
                    <div class="col-md-12">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <th>ID в СУЗ</th>
                                    <th>Номер заказа в Форвард</th>
                                    <th>Мнемоника</th>
                                    <th>Серийный номер</th>
                                    <th>Филиал</th>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>{{ $kit->id }}</td>
                                        <td>{{ $kit->id_flow }}</td>
                                        <td>{{ $kit->v_type }}</td>
                                        <td>{{ $kit->v_serial }}</td>
                                        <td>{{ $kit->v_department }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <h2 class="display-4 mt-4">Комплектующие</h2>
                        <ul>
                        @foreach($kit->equipments as $eq)
                            <li>{{ $eq->v_equipment_number }}</li>
                        @endforeach
                        </ul>
                        <hr>
                        <h2 class="display-4 mt-4">История комплекта</h2>
                        <div class="my-3 pt-5" style="overflow-x:auto;min-height:200px;">
                            <div style="width:max-content;">
                                <div class="d-inline-block alert alert-light">Склад {{ $kit->v_department }}</div>
                                @foreach($story as $st)
                                    @if($st->owner != '-')
                                        <div class="d-inline-block text-center position-relative" style="width:100px;">
                                            <div data-toggle="tooltip" data-placement="top" title="Автор: {{ $st->author }}"><i class="fas fa-arrow-right fa-2x"></i></div>
                                            <div class="h5 position-absolute">{{ $st->created_at }}</div>
                                        </div>
                                        <div class="d-inline-block alert {{ $st == $story->last() ? 'alert-success' : 'alert-light' }}">{{ $st->owner }}</div>
                                    @endif
                                    @if($st->stock != '-')
                                        <div class="d-inline-block text-center position-relative" style="width:100px;">
                                            <div data-toggle="tooltip" data-placement="top" title="Автор: {{ $st->author }}"><i class="fas fa-arrow-right fa-2x"></i></div>
                                            <div class="h5 position-absolute">{{ $st->created_at }}</div>
                                        </div>
                                        <div class="d-inline-block alert {{ $st == $story->last() ? 'alert-success' : 'alert-light' }}">{{ $st->stock }}</div>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection