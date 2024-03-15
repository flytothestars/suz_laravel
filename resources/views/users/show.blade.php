@extends('layouts.app')

@section('pageTitle', $user->name)

@section('top-content')
    <a href="{{ route('users.index') }}" class="btn btn-secondary top-back-btn"><i class="fas fa-arrow-left"></i> Назад</a>
@endsection

@php
    $department_id = isset($_GET['department_id']) ? $_GET['department_id'] : null;
    $location_id = isset($_GET['location_id']) ? $_GET['location_id'] : null;
    $current_date = isset($_GET['date']) ? $_GET['date'] : date("Y-m-d");
@endphp
@section('content')
    <div class="container-fluid mt--7" id="main_container">
        <div class="row">
            <div class="col-md-4">
                <div class="card card-profile shadow">
                    <div class="px-4">
                        <div class="text-center mt-5 border-bottom pb-5">
                            <h1 class="mt-0">{{ $user->name }}</h1>
                            <h2><a href="tel:{{ str_replace([' ', '+'], '', $user->phone) }}">{{ $user->phone }}</a>
                            </h2>
                            @if($user->locations->count() > 0)
                                <div class="h5 font-weight-400">
                                    <i class="fas fa-map-marker-alt"></i>
                                    @foreach($user->locations as $key => $loc)
                                        {{ $loc->v_name }}
                                    @endforeach
                                </div>
                            @else
                                <h5 class="font-weight-400">Не привязан к участку</h5>
                            @endif
                            <div class="h4 mt-2">{!! $user->roles_html !!}</div>
                            <!-- <span><i class="fas fa-circle text-{{ !$user->isBusy() ? 'success' : 'red' }}"></i> {{ !$user->isBusy() ? 'Свободен' : 'Занят' }}</span> -->
                        </div>
                        @if(\Auth::user()->hasAnyRole(['администратор', 'супервизер']))
                            <div class="py-5 text-center">
                                <form action="{{ route('user.update') }}" method="post">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $user->id }}">
                                    <input type="hidden" id="location_id" value="{{ $user->location->id ?? '' }}">
                                    <input type="hidden" id="department_id" value="{{ $user->department->id ?? '' }}">
                                    <div class="form-group">
                                        <label for="department">Филиал</label>
                                        <select class="form-control" name="department" id="department">
                                            @foreach($departments as $department)
                                                <option
                                                    {{ $user->department && $department->id_department == $user->department->id ? 'selected' : '' }} value="{{ $department->id_department }}">{{ $department->v_name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    @if(in_array('инспектор', $roles_arr))
                                        <div class="form-group">
                                            <label for="departments">Филиалы инспектора</label>
                                            <select class="form-control" multiple name="departments[]"
                                                    style="min-height: 150px;">
                                                @if($departments)
                                                    @foreach($departments as $department)
                                                        <option
                                                            {{ $user->departments->contains('id', $department->id) ? 'selected' : '' }} value="{{ $department->id }}">{{ $department->v_name }}</option>
                                                    @endforeach
                                                @endif
                                            </select>
                                        </div>
                                    @endif
                                    <div class="form-group">
                                        <label for="location">Участок</label>
                                        <select class="form-control" multiple name="location[]" id="location"
                                                style="min-height: 150px;">
                                            @if($locations)
                                                @foreach($locations as $location)
                                                    <option
                                                        {{ $user->locations->contains('id', $location->id) ? 'selected' : '' }} value="{{ $location->id }}">{{ $location->v_name }}</option>
                                                @endforeach
                                            @endif
                                        </select>
                                    </div>
                                    <button class="btn btn-success" disabled>Сохранить</button>
                                    @if(Auth::user()->hasRole('администратор'))
                                        <a href="/auth_by_id/{{ $user->id }}" class="btn btn-primary">Авторизоваться</a>
                                    @endif
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <ul class="nav nav-tabs bg-gradient-white rounded" id="myTab" role="tablist">
                    <li class="nav-item">
                        <a class="nav-link active" id="kits-tab" data-toggle="tab" href="#kits" role="tab" aria-controls="kits" aria-selected="true">Комплекты</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" id="materials-tab" data-toggle="tab" href="#materials" role="tab" aria-controls="materials" aria-selected="false">Расходники</a>
                    </li>
                </ul>
                <div class="card tab-content" id="myTabContent" style="max-height:650px;overflow-y:scroll;">
                    <div class="px-4 tab-pane fade show active" id="kits" role="tabpanel" aria-labelledby="kits-tab">
                        <div class="text-center border-bottom mt-5 pb-5">
                            <h1>Оборудование</h1>
                        </div>
                        @if($user->kits->count() > 0)
                            <div class="table-responsive mb-4">
                                <table class="table table-bordered">
                                    <thead>
                                    <th>Мнемоника</th>
                                    <th>Серийный номер</th>
                                    <th>Комплектующие</th>
                                    <th>Информация о перемещении</th>
                                    </thead>
                                    @foreach($user->kits as $kit)
                                        <tr>
                                            <td>
                                                {{ $kit->name }}
                                            </td>
                                            <td>
                                                {{ $kit->v_serial }}
                                            </td>
                                            <td>
                                                @foreach($kit->equipments as $eq)
                                                    <div class="badge badge-success d-block mb-1"
                                                         style="font-size:10pt;">{{ $eq->v_equipment_number }}</div>
                                                @endforeach
                                            </td>
                                            <td>
                                                @php
                                                    $prev = $kit->getPrevious();
                                                @endphp
                                                @if($prev)
                                                    <div>Откуда:
                                                        @if($prev->owner_id)
                                                            <b>
                                                                {{
                                                                    strlen($prev->owner_id) == 9
                                                                    ? ($userPrev = \App\Models\User::find($prev->owner_id)) ? $userPrev->name : 'Нет информации'
                                                                    : $prev->owner_id
                                                                }}
                                                            </b>
                                                        @else
                                                            <b>{{ \App\Models\Stock::find($prev->stock_id)->name }}</b>
                                                        @endif
                                                    </div>
                                                    <div>Дата и время: <b>{{ $prev->created_at }}</b></div>
                                                @else
                                                    <div class="text-muted">Нет информации</div>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>
                        @else
                            <div class="text-center text-muted py-4 my-4">Список оборудования пуст.</div>
                        @endif
                    </div>
                    <div class="px-4 tab-pane fade" id="materials" role="tabpanel" aria-labelledby="materials-tab">
                        <div class="text-center border-bottom mt-5 pb-5">
                            <h1>Материалы</h1>
                        </div>
                        @if($user->getMaterials()->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                <th>Название</th>
                                <th>Тип</th>
                                <th>Количество</th>
                                </thead>
                                <tbody>
                                    @foreach($user->getMaterials() as $m)
                                        <tr>
                                            <td>{{ $m->name }}</td>
                                            <td><b>{{ $m->type }}</b></td>
                                            <td>{{ $m->qty }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @else
                            <div class="text-center text-muted py-4 my-4">Список материалов пуст.</div>
                        @endif
                    </div>

                </div>
            </div>
            <div class="col-md-6 my-4">
                <div class="card shadow px-4">
                    <div class="text-center mt-4 pb-3">
                        <h1>Маршрутные листы</h1>
                    </div>
                    <div class="pb-4">
                        @php
                            $routeLists = $user->getRouteLists();
                        @endphp
                        @if($routeLists)
                            <table class="table table-bordered">
                                @foreach($routeLists as $rl)
                                    <tr>
                                        <td>
                                            <a href="/routelists/{{ $rl->id }}">Маршрут №{{ $rl->id }}</a>
                                        </td>
                                        <td>
                                            <span
                                                class="{{ $rl->date == date('Y-m-d') ? 'alert-danger' : '' }}">{{ $rl->date }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </table>
                        @else
                            <div>Нет маршрутных листов.</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            $("select").on("change", function () {
                $("button").removeAttr("disabled");
            });
            $("#department").on("change", function () {
                let department_id = $(this).val();
                $.ajax({
                    url: "/get_location_by_department",
                    data: {
                        department_id: department_id
                    },
                    dataType: 'json',
                    success(data) {
                        let html = "";
                        let slctd = "";
                        for (let i = 0; i < data.length; i++) {
                            if (location_id == data[i]['id_location']) {
                                slctd = "selected";
                            } else {
                                slctd = "";
                            }
                            html += "<option " + slctd + " value='" + data[i]['id'] + "'>" + data[i]['v_name'] + "</option>";
                        }
                        $("#location").html(html);
                    }
                });
            });
        });
    </script>
@endsection
