@extends('layouts.app')

@section('pageTitle', 'История заявки №' . request()->id)

@section('styles')
    <style>
        .red{
            background-color: #ff000038;
        }
    </style>
@endsection

@section('top-content')
    <a href="/requests/{{ request()->id }}" class="btn btn-secondary top-back-btn"><i class="fas fa-arrow-left"></i> Назад</a>
@endsection

@section('content')
    <div class="container-fluid mt-md--7 main">
        <div class="card min-height-500 mb-5 shadow">
            <div class="card-header bg-transparent border-0 mb-0">
                <h1 class="mt-3 mb-0">История заявки №{{ request()->id }}</h1>
            </div>
            <div class="card-body">
                @if($requests->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered">
                        <thead>
                            <th>Дата и время</th>
                            <th>Статус</th>
                            <th>Тип работ</th>
                            <th>Филиал</th>
                            <th>Контракт</th>
                            <th>Участок</th>
                            <th>Сектор</th>
                            <th>Регион</th>
                            <th>Район</th>
                            <th>Город</th>
                            <th>Улица</th>
                            <th>Дом</th>
                            <th>Квартира</th>
                            <th>Вид работ</th>
                            <th>Описание</th>
                            <th>Описание ко времени</th>
                            <th>Дата работ</th>
                            <th>Продукт</th>
                            <th>Тариф</th>
                            <th>Монтажники</th>
                            <th>Диспетчер</th>
                            <th>Комментарий</th>
                            <th>Автор коммента</th>
                        </thead>
                        <tbody>
                        @foreach($requests as $key => $r)
                            <tr>
                                <td>{{ $r->dt_start }}</td>
                                <td class="{{ isset($requests[$key-1]->status) && $r->status != $requests[$key-1]->status ? 'red' : '' }}">{{ $r->status }}</td>
                                <td>{{ $r->ci_flow }}</td>
                                <td>{{ $r->department->name }}</td>
                                <td class="{{ isset($requests[$key-1]->contract) && $r->contract != $requests[$key-1]->contract ? 'red' : '' }}">{{ $r->contract }}</td>
                                <td>{{ $r->location }}</td>
                                <td>{{ $r->sector }}</td>
                                <td>{{ $r->region }}</td>
                                <td>{{ $r->district }}</td>
                                <td>{{ $r->town }}</td>
                                <td>{{ $r->street }}</td>
                                <td>{{ $r->house }}</td>
                                <td>{{ $r->flat }}</td>
                                <td>{{ $r->type_works }}</td>
                                <td>{{ $r->description }}</td>
                                <td>{{ $r->description_time }}</td>
                                <td class="{{ isset($requests[$key-1]->dt_plan_date) && $r->dt_plan_date != $requests[$key-1]->dt_plan_date ? 'red' : '' }}">{{ $r->dt_plan_date }}</td>
                                <td>{{ $r->product }}</td>
                                <td>{{ $r->tariff }}</td>
                                <td>
                                	@if($r->installer_1)<span class="d-block">{{ $r->installer_1 }}</span>@endif
                                	@if($r->installer_2)<span class="d-block">{{ $r->installer_2 }}</span>@endif
                                </td>
                                <td class="{{ isset($requests[$key-1]->dispatcher->name) && $r->dispatcher->name != $requests[$key-1]->dispatcher->name ? 'red' : '' }}">{{ $r->dispatcher }}</td>
                                <td class="{{ isset($requests[$key-1]) && $r->comment != $requests[$key-1]->comment ? 'red' : '' }}">{{ $r->comment }}</td>
                                <td class="{{ isset($requests[$key-1]) && $r->comment_author != $requests[$key-1]->comment_author ? 'red' : '' }}">{{ $r->comment_author }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="h2">С этой заявкой еще ничего не делали.</div>
                @endif
            </div>
        </div>
    </div>
@endsection