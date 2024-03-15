@extends('layouts.app')

@section('pageTitle', $material->name)

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
                <h1 class="display-3 mb-1">{{ $material->name }}</h1>
                <h5 class="text-muted">{{ $material->type }}</h5>
                <div class="mb-5">Всего в системе: {{ $total_count }} шт.</div>
                <div class="row">
                    <div class="col-md-12">
                        <h4>Техники, имеющие эти расходники:</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <th>#</th>
                                    <th>Имя</th>
                                    <th>Филиал</th>
                                    <th>Количество</th>
                                </thead>
                                <tbody>
                                    @if($users->count() > 0)
                                        @php
                                            $n = 1;
                                        @endphp
                                        @foreach($users as $user)
                                        <tr>
                                            <td>{{ $n }}</td>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->department->name }}</td>
                                            <td><span class="text-danger h4">{{ $user->qty }}</span> шт.</td>
                                        </tr>
                                            @php
                                                $n++;
                                            @endphp
                                        @endforeach
                                    @else
                                        <tr class="text-center">
                                            <td colspan="3">Нет таких техников.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <hr>
                    </div>
                    <div class="col-md-12">
                        <h4>Клиенты, имеющие эти расходники:</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <th>#</th>
                                    <th>Номер договора</th>
                                    <th>Количество</th>
                                </thead>
                                <tbody>
                                    @if($clients->count() > 0)
                                        @foreach($clients as $key => $client)
                                        <tr>
                                            <td>{{ $key+1 }}</td>
                                            <td>{{ $client->contract }}</td>
                                            <td><span class="text-danger h4">{{ $client->qty }}</span> шт.</td>
                                        </tr>
                                        @endforeach
                                    @else
                                        <tr class="text-center">
                                            <td colspan="3">Нет таких клиентов.</td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <hr>
                    </div>
                    <div class="col-md-12">
                        <h4>Склады, имеющие эти расходники:</h4>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <th>#</th>
                                    <th>Склад</th>
                                    <th>Количество</th>
                                </thead>
                                <tbody>
                                    @if($stocks->count() > 0)
                                        @php
                                            $n = 1;
                                        @endphp
                                        @foreach($stocks as $stock)
                                        <tr>
                                            <td>{{ $n }}</td>
                                            <td>{{ $stock->name }}</td>
                                            <td><span class="text-danger h4">{{ $stock->qty }}</span> шт.</td>
                                        </tr>
                                        @php
                                            $n++;
                                        @endphp
                                        @endforeach
                                    @else
                                        <tr class="text-center">
                                            <td colspan="3">Нет таких складов.</td>
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