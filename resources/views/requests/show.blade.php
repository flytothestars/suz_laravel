@extends('layouts.app')

@section('pageTitle', 'Заявка №' . $request->id)

@section('top-content')
    @if(\Auth::user()->hasRole('техник'))
        <a href="/routelist" class="btn btn-secondary top-back-btn"><i class="fas fa-arrow-left"></i> Назад</a>
    @elseif(\Auth::user()->hasRole('просмотр маршрута'))
        <a href="/routing" class="btn btn-secondary top-back-btn"><i class="fas fa-arrow-left"></i> Назад</a>
    @else
        <a href="/requests" class="btn btn-secondary top-back-btn"><i class="fas fa-arrow-left"></i> Назад</a>
    @endif
@endsection

@section('content')
    <div class="container-fluid mt-md--7 main">
        <div class="card min-height-500 mb-5 shadow">
            <div class="card-header bg-transparent border-0">
                @if(session('message'))
                    <div class="row">
                        <div class="col-md-12">
                            <div
                                class="alert alert-{{ session('message') == 'Заявка завершена успешно.' || session('message') == 'Регистрация прошла успешно.' ? 'success' : 'danger' }}">{{ session('message') }}</div>
                        </div>
                    </div>
                @endif
                <div class="row">
                    <div class="col-md-12">
                        @if(!$request->is_my_request)
                            <div class="alert alert-warning d-inline-block"><i class="fas fa-exclamation-circle"></i>
                                Данная заявка не принадлежит вам. Вы не можете ею управлять.
                            </div>
                        @endif
                        <h1 class="mt-3 mb-0">Заявка №{{ $request->id }}</h1>
                        <h5 class="mb-0 text-muted">Наряд №{{ $request->id_flow }}</h5>
                        @if(\Auth::user()->hasAnyRole(['администратор', 'диспетчер']) && $request->routeList)
                            <a href="/routelists/{{ $request->routeList->id }}" target="_blank" data-toggle="tooltip"
                               data-placement="bottom" title="{{ $request->routeList->date }}">Маршрутный лист
                                №{{ $request->routeList->id }}</a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-8">
                    <div class="table-responsive border-right border-top" id="single_request_table">
                        <table class="table align-items-center table-flush">
                            <input type="hidden" name="request_id" value="{{ $request->id }}">
                            @csrf
                            <tbody>
                            <tr>
                                <td>Статус заказа</td>
                                <td>
                                    <span data-toggle="tooltip" data-placement="bottom"
                                          title="{{ $request->dt_status_start ? $request->dt_status_start : '' }}"
                                          class="badge-status badge badge-pill badge-{{ getStatusClass($request->status) }}
                                            text-uppercase">{{ $request->status }}</span>
                                </td>
                            </tr>
                            <tr>
                                <td>Тип заказа</td>
                                <td id="ci_flow">{{ $request->ci_flow }}</td>
                            </tr>
                            <tr>
                                <td>Тип работ</td>
                                <td>{{ $request->kind_works }}</td>
                            </tr>
                            <tr>
                                <td>Виды работ</td>
                                <td>{{ $request->type_works }}</td>
                            </tr>
                            <tr>
                                <td>Запланированный интервал выполнения работ</td>
                                <td>{{ $request->n_plan_time }}</td>
                            </tr>
                            <tr>
                                <td>Запланированная дата работ</td>
                                <td>
                                    {{ $request->dt_plan_date }}
                                    @if($request->status == 'Назначено')
                                        @if(date('Y-m-d', strtotime($request->dt_plan_date)) < date('Y-m-d'))
                                            <i title="Заявка просрочена!" data-toggle="tooltip"
                                               class="fas fa-calendar-times text-danger"></i>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>Продукт</td>
                                <td>{{ $request->product }}</td>
                            </tr>
                            <tr>
                                <td>Дата создания</td>
                                <td>{{ $request->dt_start }}</td>
                            </tr>
                            @if($request->dt_stop != '2555-01-01 00:00:00')
                                <tr>
                                    <td>Дата обновления</td>
                                    <td>{{ $request->dt_stop }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td>Филиал</td>
                                <td>{{ $request->department->name }}</td>
                            </tr>
                            <tr>
                                <td>Точка агрегации</td>
                                <td>{{ $request->location }}</td>
                            </tr>
                            <tr>
                                <td>Сектор</td>
                                <td>{{ $request->sector }}</td>
                            </tr>
                            @if($request->region != 'Undefined')
                                <tr>
                                    <td>Область</td>
                                    <td>{{ $request->region }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td>Район</td>
                                <td>{{ $request->district }}</td>
                            </tr>
                            <tr>
                                <td>Город</td>
                                <td>{{ $request->town }}</td>
                            </tr>
                            <tr>
                                <td>Улица</td>
                                <td>{{ $request->street }}</td>
                            </tr>
                            <tr>
                                <td>Дом</td>
                                <td>{{ $request->house }}</td>
                            </tr>
                            @if($request->flat != '')
                                <tr>
                                    <td>Квартира</td>
                                    <td>{{ $request->flat }}</td>
                                </tr>
                            @endif
                            <tr style="border-top:2px solid #e3e4ea;">
                                <td>ФИО клиента</td>
                                <td>
                                    <span class="d-inline-block">{{ $request->v_client_title }}</span>
                                    @if($request->is_my_request)
                                        <button class="ml-2 btn-sm btn btn-info edit-client-btn" data-toggle="modal"
                                                data-target="#clientModal"><i class="fas fa-user-edit"></i> Изменить
                                        </button>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td>Номер договора</td>
                                <td>{{ $request->contract }}</td>
                            </tr>
                            <tr>
                                <td>Номер телефона</td>
                                <td>{{ $request->v_client_cell_phone }}</td>
                            </tr>
                            <tr>
                                <td>Городской номер телефона</td>
                                <td>{{ $request->v_client_landline_phone }}</td>
                            </tr>
                            <tr style="border-top:2px solid #e3e4ea;">
                                <td>Примечание</td>
                                <td>{{ $request->description }}</td>
                            </tr>
                            @if($request->description != $request->description_time)
                                <tr>
                                    <td>Примечание к интервалу времени работ</td>
                                    <td>{{ $request->description_time }}</td>
                                </tr>
                            @endif
                            <tr>
                                <td>Тарифный план</td>
                                <td><span class="h3 text-warning">{{ $request->tariff }}</span></td>
                            </tr>
                            @if($request->service)
                                <tr>
                                    <td>Услуги</td>
                                    <td>
                                        @foreach($request->service as $key => $service)
                                            <div class="hover-dark service-block">
                                                <ul class='mb-0 py-3'>
                                                    <li class='mb-0 h4 text-primary'>{{ $service->name }}</li>
                                                    <li class='mb-0'>Технология: {{ $service->technology }}</li>
                                                    @if($service->technology === 'Internet/TV - GPON')
                                                        <button type="button" id="getGponLogin" class="button button-primary btn btn-sm" data-id-house="{{ $request->id_house }}" disabled>
                                                            Получить login GPON
                                                        </button>
                                                    @endif
                                                    @if($service->equipment_list)
                                                        <li class='mb-0'>
                                                            Оборудование:<br>
                                                            @foreach($service->equipment_list as $eq)
                                                                <ul class="mb-1 d-inline-block">
                                                                    <li class="mb-0"><span class="font-weight-light">Модель:</span> {{ $eq->equipment_model->v_name ?? '-' }} {{ $eq->equipment_model->v_vendor ?? '-' }}
                                                                    </li>
                                                                    <li class="mb-0"><span class="font-weight-light">Серийный номер:</span> {{ $eq->equipment_number }}
                                                                    </li>
                                                                    <li class="mb-0"><span class="font-weight-light">Тип передачи:</span> {{ $eq->equipment_transfer }}
                                                                    </li>
                                                                    <li class="mb-0"><span class="font-weight-light">Номер комплекта:</span> {{ $eq->equipment_serial }}
                                                                    </li>
                                                                    @if(\Auth::user()->hasRole('администратор'))
                                                                        <li>{!! $eq->exists ? '<i class="fas fa-check text-success"></i> Есть в базе' : '<i class="fas fa-times text-danger"></i> Отсутствует в базе' !!}</li>
                                                                        @if(!$eq->exists)
                                                                            <button
                                                                                class="btn btn-warning btn-sm fix-btn"
                                                                                data-equipment_number="{{ $eq->equipment_number }}">
                                                                                Исправить
                                                                            </button>
                                                                        @endif
                                                                    @endif
                                                                </ul>
                                                            @endforeach
                                                        </li>
                                                    @endif
                                                </ul>
                                                @if($request->id_type_works == 2023 && $request->is_my_request)
                                                    <div class="form-group mt-4 mb-0">
                                                        <input type="checkbox" name="use_clients_equipment[]"
                                                               id="use_clients_equipment_{{ $key }}">
                                                        <label for="use_clients_equipment_{{ $key }}">Использовать
                                                            оборудование клиента</label>
                                                    </div>
                                                @endif
                                                @if(Auth::user()->hasAnyRole(['администратор', 'техник']))
                                                    <div class="mb-3">
                                                        @if($request->status == 'Назначено')
                                                            @if($service->name == 'Интернет')
                                                                <div class="form-group">
                                                                    <label for="v_param_internet">Параметр подключения
                                                                        услуги интернет:</label>
                                                                    <input type="text" name="v_param_internet"
                                                                           class="form-control" id="v_param_internet">
                                                                </div>
                                                            @endif
                                                            @if($request->is_my_request)
                                                                <button class="mt-3 btn btn-info use-equipment-btn"
                                                                        data-service_key="{{ $key }}">Добавить/забрать
                                                                    оборудование
                                                                </button>
                                                                <button class="mt-3 btn btn-success use-material-btn">
                                                                    Добавить/забрать расходники
                                                                </button>
                                                            @endif
                                                        @endif
                                                    </div>
                                                @endif
                                                <div class="border-bottom"></div>
                                                <br>
                                            </div>
                                        @endforeach
                                        @if(\Auth::user()->hasRole('администратор'))
                                            <div class="my-4">
                                                <label>Для администраторов:</label>
                                                <div>
                                                    <button class="btn btn-secondary btn-sm show-json"
                                                            data-json="{{ $request->service_info }}">Посмотреть JSON
                                                        услуг
                                                    </button>
                                                    <a href="http://{{ env('WS_HOST') }}/show/create_flow/?id={{ $request->id_flow }}&date={{ date('Y-m-d', strtotime($request->dt_start)) }}"
                                                       target="_blank" class="btn btn-secondary btn-sm">Посмотреть
                                                        createFlow</a>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endif
                            @if($request->service_info_add)
                                <tr>
                                    <td>Тарифные пакеты</td>
                                    <td>
                                        <ul class="mb-0 d-inline-block">
                                            @foreach($request->service_info_add as $sia)
                                                <li class="mb-0">{{ $sia->service }}</li>
                                            @endforeach
                                        </ul>
                                    </td>
                                </tr>
                            @endif
                            @if($request->v_client_switch_port != '')
                                <tr>
                                    <td>Порт клиента на оборудовании MetroEthernet</td>
                                    <td>{{ $request->v_client_switch_port }}</td>
                                </tr>
                            @endif
                            @if($request->v_client_switch_mac != '')
                                <tr>
                                    <td>Мак-адрес коммутатора MetroEthernet</td>
                                    <td>{{ $request->v_client_switch_mac }}</td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                        @if(\Auth::user()->hasAnyRole(['администратор', 'техник', 'диспетчер', 'кладовщик', 'инспектор']))
                            <div class="row justify-content-md-center mb-4 border-top pt-4">
                                <div class="col-md-11">
                                    <div class="h5">Оборудование</div>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover cursor-pointer mb-3"
                                               id="equipment_table">
                                            <thead>
                                            <th>Направление</th>
                                            <th>Тип</th>
                                            <th>Название</th>
                                            <th>Количество</th>
                                            <th>Монтажник</th>
                                            <th>Экземпляры</th>
                                            </thead>
                                            <tbody>
                                            @if($request->status_id == 5)
                                                @foreach($request->equipments->groupBy('id_equipment_model') as $id_equipment_model => $eq)
                                                    <tr>
                                                        <td>У клиента</td>
                                                        <td>-</td>
                                                        <td>{{ $eq[0]->model->v_name ?? 'Модель неизвестна' }}
                                                            - {{ $eq[0]->model->v_vendor ?? 'Вендор неизвестен' }}</td>
                                                        <td>{{ $eq->count() }}</td>
                                                        <td>-</td>
                                                        <td>
                                                            @foreach($eq as $item)
                                                                {{ $item->v_equipment_number }}<br>
                                                            @endforeach
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                            </tbody>
                                        </table>
                                    </div>
                                    @if($request->status_id == 4)
                                        @if(\Auth::user()->hasAnyRole(['администратор', 'техник', 'диспетчер', 'кладовщик']))
                                            <button class="float-right btn btn-sm btn-danger mr-2"
                                                    id="remove_equipment_btn"><i class="fas fa-trash"></i></button>
                                        @endif
                                    @endif
                                </div>
                            </div>
                            <div class="row justify-content-md-center mb-4 pt-4">
                                <div class="col-md-11">
                                    <div class="h5">Расходники</div>
                                    <div class="table-responsive">
                                        <table class="table table-bordered materials-div">
                                            <thead>
                                            <th>Направление</th>
                                            <th>Название</th>
                                            <th>Количество</th>
                                            <th>Монтажник</th>
                                            <th>Лимиты</th>
                                            </thead>
                                            <tbody></tbody>
                                        </table>
                                        @if(\Auth::user()->hasAnyRole(['администратор', 'техник', 'диспетчер', 'кладовщик']))
                                            <button class="btn btn-danger btn-sm float-right mr-2 mt-3"
                                                    id="remove_materials_btn"><i class="fas fa-trash"></i></button>
                                        @endif
                                    </div>
                                    @if($request->client_materials && $request->client_materials->count() > 0)
                                        <div class="h5 mt-4">В наличии у абонента</div>
                                        <ul class="sub-in-stock">
                                            @foreach($request->client_materials as $m)
                                                <li data-binded="{{ $m->bindedAmount ?? '' }}">{{ $m->name }}
                                                    - {{ $m->qty }} шт.
                                                </li>
                                            @endforeach
                                        </ul>
                                    @endif

                                    @if($request->limitcrossed && $request->limitcrossed->count() > 0)
                                        <div class="h5 mt-4">Имеется превышение</div>
                                        <ul class="limit-crossed">
                                            @foreach($request->limitcrossed as $crossed)
                                                <li>{{$crossed->material->name}} - {{$crossed->qty_sum}} шт.</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-md-4">
                    @if($request->dispatcher)
                        <div class="card mb-3 mr-md-4 mx-2">
                            <div class="card-body">
                                <h4>Диспетчер:</h4>
                                {{ $request->dispatcher->name }}
                            </div>
                        </div>
                    @endif
                    @if($request->installers)
                        <div class="card mb-3 mr-md-4 mx-2">
                            <div class="card-body">
                                <h4>Монтажники:</h4>
                                @foreach($request->installers as $inst)
                                    <a href="/users/{{ $inst->id }}" class="d-block">{{ $inst->name ?? '-' }}</a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    @if($request->date_time)
                        <div class="card my-3 mr-md-4 mx-2">
                            <div class="card-body">
                                <h4>Дата и время:</h4>
                                <div
                                    class="badge badge-pill badge-primary text-uppercase">{{ $request->date_time }}</div>
                            </div>
                        </div>
                    @endif
                    @if($request->cancel_reason)
                        <div class="card my-3 mr-md-4 mx-2">
                            <div class="card-body">
                                <h4>Причина отмены:</h4>
                                {{ $request->cancel_reason->v_name }}
                            </div>
                        </div>
                    @endif
                    @if($request->reason)
                        <div class="card my-3 mr-md-4 mx-2">
                            <div class="card-body">
                                <h4>Примечание:</h4>
                                {{ $request->reason }}
                            </div>
                        </div>
                    @endif

                    <div class="my-3 mr-md-4 mx-2 mb-5">
                        <a href="/requests/{{ $request->id }}/story" class="btn btn-white btn-lg"><i
                                class="fas fa-history"></i> Посмотреть историю</a>
                    </div>
                    <div class="my-3 mr-md-4 mx-2 mb-5">
                        <form id="word_form" method="post" action="{{action('SuzRequestController@downloadWord')}}"
                              class="form">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <input type="hidden" name="ids[]" value="{{ $request->id }}">
                            <button type="submit" class="btn btn-primary" id="word"><i class="far fa-file-word"></i>
                                Распечатать заявку
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-footer py-5">
                @if($request->is_my_request)
                    <div class="row text-md-right">
                        <div class="col-md-12">
                            @if((Auth::user()->hasRole('диспетчер') || Auth::user()->hasRole('администратор')))
                                @if($request->status != 'Отменено' && $request->status != 'Выполнено' && $request->status != 'Новый' && $request->status != 'В ожидании назначения')
                                    <a href="/requests/{{ $request->id }}/postpone/" class="btn mb-3 btn-warning"
                                       id="postpone_request">Отложить заявку</a>
                                @endif
                                @if($request->status != 'Отменено' && $request->status != 'Выполнено' && $request->status != 'В ожидании назначения')
                                    <a href="/requests/{{ $request->id }}/cancel/" class="btn mb-3 btn-info"
                                       id="cancel_request">Отменить заявку</a>
                                @endif
                            @endif
                            @if(Auth::user()->hasAnyRole(['администратор', 'техник']))
                                @if($request->status != 'Договорено' && $request->status != 'Новый' && $request->status != 'Выполнено' && $request->status != 'Отменено' && $request->status != 'В ожидании назначения')
                                    <form action="/requests/{{ $request->id }}/return" method="post"
                                          class="d-inline-block mr-2">
                                        @csrf
                                        <input type="hidden" name="installer_take">
                                        <input type="hidden" name="installer_give">
                                        <input type="hidden" name="kits_to_give">
                                        <input type="hidden" name="materials_take">
                                        <input type="hidden" name="materials_qty_take">
                                        <input type="hidden" name="materials_give">
                                        <input type="hidden" name="materials_qty_give">
                                        <input type="hidden" name="status_type">
                                        <input type="hidden" name="limit_input">

                                        <button type="button" class="btn complete-btn mb-3 btn-lg">
                                            Сделать возврат
                                        </button>
                                    </form>

                                    <form action="/requests/{{ $request->id }}/complete" method="post"
                                          class="d-inline-block mr-2">
                                        @csrf
                                        <input type="hidden" name="installer_take">
                                        <input type="hidden" name="installer_give">
                                        <input type="hidden" name="kits_to_give">
                                        <input type="hidden" name="equipments_to_take">
                                        <input type="hidden" name="kits_to_take">
                                        <input type="hidden" name="v_kits_transfer">
                                        <input type="hidden" name="b_unbind_cntr">
                                        <input type="hidden" name="v_param_internet">
                                        <input type="hidden" name="materials_take">
                                        <input type="hidden" name="materials_qty_take">
                                        <input type="hidden" name="materials_give">
                                        <input type="hidden" name="materials_qty_give">

                                        <input type="hidden" name="dt_birthday" value="{{ $request->dt_birthday }}">
                                        <input type="hidden" name="v_iin" value="{{ $request->v_iin }}">
                                        <input type="hidden" name="v_document_number"
                                               value="{{ $request->v_document_number }}">
                                        <input type="hidden" name="dt_document_issue_date"
                                               value="{{ $request->dt_document_issue_date }}">
                                        <input type="hidden" name="v_document_series"
                                               value="{{ $request->v_document_series }}">

                                        @if($request->installers)
                                            @foreach($request->installers as $installers)
                                                <input type="hidden" name="installers[]"
                                                       value="{{ $installers->username }}">
                                            @endforeach
                                        @endif

                                        <input type="hidden" name="limit_input">
                                        <input type="hidden" name="use_clients_equipment">
                                        <input type="hidden" name="status_type">

                                        <button type="button" class="btn mb-3 btn-success complete-btn btn-lg">
                                            <i class="fas fa-check"></i>
                                            Завершить заявку
                                        </button>
                                    </form>
                                @endif
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
        @if($request->repair_name->count() > 0)
            <div class="h4 pb-2 pl-1">Типы проведенных ремонтных работ:</div>
            <div class="row">
                <div class="col-md-12">
                    <div class="card shadow-sm mb-4">
                        <div class="card-body">
                            @foreach($request->repair_name as $repair_name)
                                <option>— {{ $repair_name->v_name }}</option><br>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif
        @if($request->getComments()->count() > 0)
            <div class="row">
                <div class="col-md-12">
                    <div class="h4 pb-2 pl-1">Комментарии:</div>
                    @foreach($request->getComments() as $comment)
                        <div class="card shadow-sm mb-4">
                            <div class="card-body">
                                <div>
                                    <div class="float-right">Статус: {{ $comment->status }}</div>
                                    <div class="d-flex flex-row align-items-center">
                                        <div class="mr-2 user-icon user_bgcolor_{{ rand(1,8) }}">
                                            @php
                                                $words = cyr2lat(explode(" ", trim($comment->author)));
                                                $acronym = "";
                                                foreach($words as $w)
                                                {
                                                    $acronym .= $w[0];
                                                }
                                            @endphp
                                            <div>{{ $acronym }}</div>
                                        </div>
                                        <div>
                                            <a href="/users/{{ $comment->author_id }}"
                                               class="d-block h3 font-weight-bold text-success mb-0">{{ $comment->author }}</a>
                                            <span
                                                class="text-muted mt-0 h5 font-weight-light">{{ $comment->date }}</span>
                                        </div>
                                    </div>
                                    <div class="py-3">{{ $comment->message }}</div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        @if($request->images->count() > 0 && $request->status_id == 5)
            <div class="h4 pb-2 pl-1">Прикрепленные фотографии:</div>
            <div class="row">
                @foreach($request->images as $image)
                    <div class="col-md-4">
                        <div class="card shadow-sm mb-4">
                            <div class="card-body">
                                <img class="card-img-top" id="image" src="/images/{{ $image }}"/>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- My Equipment Modal -->
    <div class="modal fade" id="myKitsModal" tabindex="-1" role="dialog" aria-labelledby="myKitsModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content"></div>
        </div>
    </div>
    <!-- JSON modal -->
    <div class="modal fade" id="jsonModal" tabindex="-1" role="dialog" aria-labelledby="jsonModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="jsonModalLabel">JSON</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" style="font-family:monospace;">
                    <textarea class="form-control" style="min-height: 250px;" readonly></textarea>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Client modal -->
    <div class="modal fade" id="clientModal" tabindex="-1" role="dialog" aria-labelledby="clientModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="clientModalLabel">Редактирование данных клиента</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form action="">
                        <div class="form-group">
                            <label>Дата рождения</label>
                            <input type="date" class="form-control" value="{{ $request->dt_birthday }}"
                                   name="dt_birthday">
                        </div>
                        <div class="form-group">
                            <label>ИИН</label>
                            <input type="text" class="form-control" value="{{ $request->v_iin }}" name="v_iin"
                                   maxlength="12">
                        </div>
                        <div class="form-group">
                            <label>Номер документа</label>
                            <input type="text" class="form-control" value="{{ $request->v_document_number }}"
                                   name="v_document_number">
                        </div>
                        <div class="form-group">
                            <label>Дата выдачи документа</label>
                            <input type="text" class="form-control" value="{{ $request->dt_document_issue_date }}"
                                   name="dt_document_issue_date">
                        </div>
                        <div class="form-group">
                            <label>Серия документа</label>
                            <input type="text" class="form-control" value="{{ $request->v_document_series ?? '' }}"
                                   name="v_document_series">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-primary">Применить</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Materials modal -->
    <div class="modal fade" id="materialsModal" tabindex="-1" role="dialog" aria-labelledby="materialsModalLabel"
         aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
            </div>
        </div>
    </div>
@endsection

@section('page-scripts')
    <script type="text/javascript">
        $(document).ready(function () {
            $('input[name="v_iin"]').on('keypress', function (event) {
                var regex = new RegExp("^[0-9]+$");
                var key = String.fromCharCode(!event.charCode ? event.which : event.charCode);
                if (!regex.test(key)) {
                    event.preventDefault();
                    return false;
                }
            });

            $(document).on('submit', '#word_form', function () {
                $('#word').attr('disabled', 'disabled');
            });

            var service_key = 0;

            $('.use-equipment-btn').on('click', function () {
                let installer_id = $("#installer").val();
                service_key = $(this).data('service_key');

                loadEquipmentsModal(installer_id, service_key);
            });

            var loadEquipmentsModal = function (installer_id, service_key) {
                let request_id = $("input[name='request_id']").val();
                let direction = $("select[name='direction']").val();
                let modal = $('#myKitsModal');

                $('.use-equipment-btn').attr('disabled', 'disabled');
                $.ajax({
                    url: '/getInstallerKits',
                    data: {
                        request_id: request_id,
                        direction: direction,
                        installer_id: installer_id,
                        service_key: service_key
                    },
                    dataType: 'json',
                    success(response) {
                        modal.find('.modal-content').html(response['html']);
                        modal.modal('show');
                        $('.use-equipment-btn').removeAttr('disabled');
                        $('.selectpicker').selectpicker();
                    }
                });
                $('.modal-dialog').draggable({
                    handle: ".modal-header"
                });
            };

            var body = $('body');

            body.on('change', "select[id='installer']", function () {
                loadEquipmentsModal($(this).val(), service_key);
            });

            body.on('change', "select[id='direction']", function () {
                let installer_id = $("select[id='installer']").val();
                loadEquipmentsModal(installer_id, service_key);
            });

            /* Give to client */
            body.on('click', '#give_equipments', function () {
                let kit_id = $("#kit").val();
                let v_kits_transfer = $("#v_kits_transfer").val();
                let v_kits_transfer_text = $("#v_kits_transfer option:selected").text();
                let direction = $("#direction option:selected").text();
                let html = "";
                let installer = $("#installer option:selected").text();
                let service_key = $("input[name='service_key']").val();

                $("#myKitsModal").find(".kit-equipment li").each(function () {
                    let equipmentArr = $(this).text().split(" [");
                    let equipment_number = equipmentArr[1].replace(']', '');

                    html += "<tr class='equipment-row direction-give' data-service_key='" + service_key + "' data-kit_id='" + kit_id + "' data-v_kits_transfer='" + v_kits_transfer + "'>" +
                        "<td>" + direction + "</td>" +
                        "<td>" + v_kits_transfer_text + "</td>" +
                        "<td>" + equipmentArr[0] + "</td>" +
                        "<td>1</td>" +
                        "<td>" + installer + "</td>" +
                        "<td>" + equipment_number + "</td>" +
                        "</tr>";

                });

                $('#equipment_table').find('tbody').append(html);
                // Enable or disable the button based on the presence of rows
                var jponButton = document.getElementById('getGponLogin');
                if (jponButton) {
                    jponButton.disabled = false;
                }
                $('#myKitsModal').modal('hide');
            });
            /* Take from client */
            body.on('click', '#take_equipments', function () {
                let b_unbind_cntr = $("#b_unbind_cntr").val();
                let b_unbind_cntr_text = $("#b_unbind_cntr option:selected").text();
                let installer = $("body").find("#installer option:selected").text();
                let equipment_name = '';
                let equipment_number = '';
                let html = '';

                if ($(".kit-equipment-take").length) {
                    let v_serial = $("#kit option:selected").data('serial');
                    let target_class = $("#kit option:selected").data('target');

                    $(target_class + " li").each(function () {
                        console.log($(this));

                        let equipmentArr = $(this).text().split(" [");
                        equipment_number = equipmentArr[1].replace(']', '');
                        equipment_name = equipmentArr[0];

                        let v_equipment_transfer = $(this).data('v_equipment_transfer');
                        html += "<tr class='equipment-row send-just-serial direction-take' data-b_unbind_cntr='" + b_unbind_cntr + "' data-service_key='" + service_key + "' data-v_serial='" + v_serial + "' data-v_equipment_transfer='" + v_equipment_transfer + "'>" +
                            "<td>Забрать у абонента</td>" +
                            "<td>" + b_unbind_cntr_text + "</td>" +
                            "<td>" + equipment_name + "</td>" +
                            "<td>1</td>" +
                            "<td>" + installer + "</td>" +
                            "<td>" + equipment_number + "</td>" +
                            "</tr>";
                    });
                } else {
                    equipment_name = $("select[id=equipments] option:selected").data("name");
                    equipment_number = $("select[id=equipments] option:selected").data("serial");

                    html = "<tr class='equipment-row direction-take' data-b_unbind_cntr='" + b_unbind_cntr + "' data-serial='" + serial + "'>" +
                        "<td>Забрать у абонента</td>" +
                        "<td>" + b_unbind_cntr_text + "</td>" +
                        "<td>" + equipment_name + "</td>" +
                        "<td>1</td>" +
                        "<td>" + installer + "</td>" +
                        "<td>" + equipment_number + "</td>" +
                        "</tr>";
                }
                $('#equipment_table').find('tbody').append(html);
                $('#myKitsModal').modal('hide');
            });

            body.on('click', '.equipment-row', function () {
                let kit_id = $(this).data('kit_id');
                let v_serial = $(this).data('v_serial');

                if (kit_id) {
                    $('.equipment-row[data-kit_id=' + kit_id + ']').toggleClass('active');
                } else if (v_serial) {
                    $('.equipment-row[data-v_serial=' + v_serial + ']').toggleClass('active');
                } else {
                    $(this).toggleClass('active');
                }
            });

            body.on('click', '#remove_equipment_btn', function () {
                $('#equipment_table').find('tr.active').remove();
            });

            body.on('change', 'select[name=kit]', function () {
                let kit_id = $(this).val();
                let direction = $('select[name=direction] option:selected').text();
                let direction_code = $('select[name=direction]').val();

                if (direction_code === 'take') {
                    let target = $('#kit option:selected').data('target');
                    $('.hidden-kit-equipment').hide();
                    $(target).show();
                } else {
                    let v_kits_transfer = $("#v_kits_transfer option:selected").text();
                    $.ajax({
                        url: '/getKitsEquipment',
                        data: {
                            kit_id: kit_id,
                            direction: direction,
                            v_kits_transfer: v_kits_transfer
                        },
                        dataType: 'json',
                        success(response) {
                            $('.kit-equipment').html(response['html']);
                        }
                    });
                }
            });

            body.on('click', '.complete-btn', function () {
                if (!countLimits()) return false;

                let kit_ids = [];
                let v_kits_transfers = [];
                let kits_to_give = [];

                $('.equipment-row.direction-give').each(function () {
                    let kit_id = $(this).data('kit_id');
                    let service_key = $(this).data('service_key');
                    kits_to_give[service_key] = (typeof kits_to_give[service_key] != 'undefined' && kits_to_give[service_key] instanceof Array) ? kits_to_give[service_key] : []

                    let v_kits_transfer = $(this).data('v_kits_transfer');

                    if (kit_id && $.inArray(kit_id, kit_ids) === -1) {
                        kit_ids.push(kit_id);
                        v_kits_transfers[service_key] = v_kits_transfer;
                        kits_to_give[service_key].push(kit_id);
                    }
                });

                if (kits_to_give.length && kits_to_give.length > 0) {
                    $('input[name=kits_to_give]').val(JSON.stringify(kits_to_give));

                    console.log('Writing to kits_to_give: ' + JSON.stringify(kits_to_give));

                    $('input[name=v_kits_transfer]').val(JSON.stringify(v_kits_transfers));
                }

                if ($('.send-just-serial').length) {
                    let kit_serials = [];
                    let b_unbind_cntr_arr = [];

                    $('.send-just-serial').each(function () {
                        let v_serial = $(this).data('v_serial');
                        let b_unbind_cntr = $(this).data('b_unbind_cntr');

                        if (v_serial && $.inArray(v_serial, kit_serials) === -1) {
                            kit_serials.push(v_serial);
                            b_unbind_cntr_arr.push(b_unbind_cntr);
                        }
                    });

                    $('input[name=kits_to_take]').val(JSON.stringify(kit_serials));

                    console.log('Writing to kits_to_take: ' + JSON.stringify(kit_serials));

                    $('input[name=b_unbind_cntr]').val(JSON.stringify(b_unbind_cntr_arr));
                } else {
                    let equipment_serials = [];
                    let b_unbind_cntr_arr = [];

                    $('.equipment-row').each(function () {
                        let serial = $(this).data('serial');
                        let b_unbind_cntr = $(this).data('b_unbind_cntr');

                        if (serial && $.inArray(serial, equipment_serials) === -1) {
                            equipment_serials.push(serial);
                            b_unbind_cntr_arr.push(b_unbind_cntr);
                        }
                    });

                    $('input[name=equipments_to_take]').val(JSON.stringify(equipment_serials));
                    $('input[name=b_unbind_cntr]').val(JSON.stringify(b_unbind_cntr_arr));
                }

                if ($("#v_param_internet").length) {
                    let v_param_internet = $("#v_param_internet").val();
                    $("input[name=v_param_internet]").val(v_param_internet);
                }

                let counter = [];
                let installer_take = [];
                let installer_give = [];
                let materials_take = [];
                let materials_qty_take = [];
                let materials_give = [];
                let materials_qty_give = [];
                let use_clients_equipment = [];

                $('.materials-div tr').each(function () {
                    let counter_id = $(this).data('counter');
                    let installer_id = $(this).data('installer_id');
                    let material_id = $(this).data('material_id');
                    let qty = $(this).data('qty');
                    let direction_code = $(this).data('direction');


                    if (direction_code === 'give') {
                        if (counter_id && $.inArray(counter_id, counter) === -1) {
                            installer_give.push(installer_id);
                            materials_give.push(material_id);
                            materials_qty_give.push(qty);
                        }
                    } else if (direction_code === 'take') {
                        if (counter_id && $.inArray(counter_id, counter) === -1) {
                            installer_take.push(installer_id);
                            materials_take.push(material_id);
                            materials_qty_take.push(qty);
                        }
                    }
                });

                $(".service-block").each(function () {
                    if ($(this).find("input[type=checkbox]").length) {
                        if ($(this).find("input[type=checkbox]:checked").length) {
                            use_clients_equipment.push('Y');
                        } else {
                            use_clients_equipment.push('N');
                        }
                    }
                });

                $('input[name=installer_give]').val(JSON.stringify(installer_give));
                $('input[name=installer_take]').val(JSON.stringify(installer_take));
                $('input[name=materials_give]').val(JSON.stringify(materials_give));
                $('input[name=materials_qty_give]').val(JSON.stringify(materials_qty_give));
                $('input[name=materials_take]').val(JSON.stringify(materials_take));
                $('input[name=materials_qty_take]').val(JSON.stringify(materials_qty_take));
                $('input[name=use_clients_equipment]').val(JSON.stringify(use_clients_equipment));

                $(this).parent().submit();
            });

            $(".show-json").on('click', function () {
                let data = $(this).data('json');

                $('#jsonModal').find('.modal-body textarea').val(data);
                $('#jsonModal').modal('show');
            });

            /* Materials Modal */
            $('.use-material-btn').on('click', function () {
                let installer_id = $("#installer_material").val();
                loadMaterialsModal(installer_id);
            });

            var loadMaterialsModal = function (installer_id) {
                let request_id = $("input[name='request_id']").val();
                let direction_material = $("select[name='direction_material']").val();
                let modal = $('#materialsModal');
                let materials = [];

                $('.materials-div tr').each(function () {
                    let material_id = $(this).data('material_id');
                    let qty = $(this).data('qty');
                    let direction = $(this).data('direction');

                    if (direction === 'give') {
                        materials.push({
                            'id': material_id,
                            'qty': qty
                        });
                    }
                });

                $('.use-material-btn').attr('disabled', 'disabled');
                $.ajax({
                    url: '/getInstallerMaterials',
                    data: {
                        request_id: request_id,
                        direction_material: direction_material,
                        installer_id: installer_id
                    },
                    dataType: 'json',
                    success(response) {
                        modal.find('.modal-content').html(response['html']);

                        materials.forEach(function (material) {
                            let materialId = material.id;
                            let materialQty = material.qty;

                            // Find the corresponding <option> element with the matching value attribute
                            let option = modal.find('select[name="materials"] option[value="' + materialId + '"]');

                            // Get the current quantity data attribute of the option
                            let currentQty = option.data('qty');

                            // Calculate the updated quantity by subtracting the material's qty
                            let updatedQty = currentQty - materialQty;

                            // Update the data attribute and the displayed quantity text
                            option.data('qty', updatedQty);
                            option.data('content', option.data('content').replace('<span>' + currentQty + '</span>', '<span>' + updatedQty + '</span>'));
                        });

                        modal.modal('show');

                        $('.use-material-btn').removeAttr('disabled');
                        $('.selectpicker').selectpicker();
                    }
                });

                $('.modal-dialog').draggable({
                    handle: ".modal-header"
                });
            };

            body.on('change', "select[id='installer_material']", function () {
                loadMaterialsModal($(this).val());
            });

            body.on('change', "select[id='direction_material']", function () {
                let installer_id = $("select[id='installer_material']").val();

                loadMaterialsModal(installer_id);
            });

            $('#materialsModal').on('changed.bs.select', 'select[name=materials]', function () {
                let qty = $(this).children("option:selected").data('qty');

                $('#materialsModal').find('input[name=qty]').attr('max', qty);
            });

            $('#materialsModal').on('change', 'select[name=client_materials]', function () {
                let qty = $(this).find(":selected").data('qty');

                $('#materialsModal').find('input[name=qty]').attr('max', qty);
            });

            var counter = 1;

            $('#materialsModal').on('click', '.btn-success', function () {
                let modal = $('#materialsModal');

                if (modal.find("select[name='materials']").val() || modal.find("select[name='client_materials']").val()) {
                    let installer_id = $("#installer_material option:selected").val();
                    let installer_name = $("#installer_material option:selected").text();
                    let direction_text = $("#direction_material option:selected").text();
                    let direction_code = direction_text === 'Выдать абоненту' ? 'give' : 'take';
                    let qty_input = modal.find("input[name='qty']");
                    let qty = qty_input.val();

                    if (qty === '') {
                        alert('Укажите количество');
                        return false;
                    }

                    qty = parseInt(qty, 10);
                    let qty_max = direction_code === 'give' ? parseInt(qty_input.attr('max'), 10) : null;

                    if (qty_max !== null) {
                        if (qty > qty_max && qty_max !== 0) {
                            alert('Вы можете указать максимум ' + qty_max + ' шт.');
                            return false;
                        }

                        if (qty_max === 0) {
                            alert('У вас больше нет этих расходников.');
                            return false;
                        }
                    }

                    let limit_qty = direction_code === 'give' ? modal.find("select[name='materials'] option:selected").attr('data-limit_qty') : null;
                    let id, name;

                    if (direction_code === 'give') {
                        id = modal.find("select[name='materials']").val();
                        name = modal.find("select[name='materials'] option:selected").attr('data-name');

                        let qty_current = modal.find("select[name='materials'] option:selected").attr('data-qty');
                        modal.find("select[name='materials'] option:selected").attr('data-qty', qty_current - qty);

                        let content = name + " (<span>" + (qty_current - qty) + "</span> шт.)";
                        qty_input.attr('max', qty_current - qty);

                        modal.find("select[name='materials'] option:selected").attr('data-content', content);
                        modal.find("select[name='materials']").parent().find('.filter-option-inner-inner').html(content);
                        modal.find("select[name='materials']").parent().find('.selected.active .text').html(content);
                    } else {
                        let selected = modal.find("select[name='client_materials'] option:selected");
                        id = modal.find("select[name='client_materials']").val();
                        name = selected.attr('data-name');

                        let qty_current = selected.attr('data-qty');
                        selected.attr('data-qty', qty_current - qty);
                        qty_input.attr('max', qty_current - qty);

                        let content = selected.attr('data-name') + ' (' + (qty_current - qty) + ' шт.)';
                        selected.text(content);
                    }

                    let html = "<tr data-counter=" + counter + " data-direction=" + direction_code + " data-material_id='" + id + "' data-name='" + name + "' data-qty='" + qty + "' data-installer_id='" + installer_id + "' data-limit_qty=" + limit_qty + ">" +
                        "<td class='text-left'>" + direction_text + "</td>" +
                        "<td>" + name + "</td>" +
                        "<td class='text-left'>" + qty + "</td>" + "<td class='text-left'>" + installer_name + "</td>" + "<td class='text-left'>" + limit_qty + "</td></tr>";

                    $('body').find('.materials-div tbody').append(html);

                    modal.modal('hide');
                    counter += 1;
                } else {
                    alert('У вас нет расходников!');
                }
            });

            $('#remove_materials_btn').on('click', function () {
                $('.materials-div tbody').empty();
            });
            var tbody = $('.materials-div tbody');

            $('#undo_request').on('click', function () {
                if (!countLimits()) return false;

                if (tbody.children().length !== 0) {
                    $('input[name=status_type]').val(JSON.stringify('undo'));
                    return false;
                }
            });
            $('#cancel_request').on('click', function () {
                if (tbody.children().length !== 0) {
                    $('input[name=status_type]').val(JSON.stringify('cancel'));
                    return false;
                }
            });
            $('#postpone_request').on('click', function () {
                if (tbody.children().length !== 0) {
                    $('input[name=status_type]').val(JSON.stringify('postpone'));
                    return false;
                }
            });
            $('#clientModal').find('.btn-primary').on('click', function () {
                let modal = $("#clientModal");
                let dt_birthday = modal.find('input[name=dt_birthday]').val();

                if (dt_birthday.length === 0) {
                    alert('Укажите дату рождения!');
                    return false;
                }

                let v_iin = modal.find('input[name=v_iin]').val();
                if (v_iin.length !== 12) {
                    alert('Укажите ИИН!');
                    return false;
                }

                let v_document_number = modal.find('input[name=v_document_number]').val();

                if (v_document_number.length === 0) {
                    alert('Укажите номер документа!');
                    return false;
                }

                let dt_document_issue_date = modal.find('input[name=dt_document_issue_date]').val();

                if (dt_document_issue_date.length === 0) {
                    alert('Укажите дату выдачи документа!');
                    return false;
                }

                let v_document_series = modal.find('input[name=v_document_series]').val();

                $('input[name=dt_birthday]').val(dt_birthday);
                $('input[name=v_iin]').val(v_iin);
                $('input[name=v_document_number]').val(v_document_number);
                $('input[name=dt_document_issue_date]').val(dt_document_issue_date);
                $('input[name=v_document_series]').val(v_document_series);

                modal.modal('hide');
            });

            $('.fix-btn').on('click', function () {
                let token = $('input[name=_token]').val();
                let request_id = $("input[name='request_id']").val();
                let v_equipment_number = $(this).data('equipment_number');

                $.ajax({
                    url: "/fix_equipment",
                    type: "post",
                    data: {
                        _token: token,
                        request_id: request_id,
                        v_equipment_number: v_equipment_number
                    },
                    success(response) {
                        alert(response['message']);
                        window.location.reload();
                    },
                    error(jqXHR, exception) {
                        var msg = '';
                        if (jqXHR.status === 0) {
                            msg = 'Not connect.\n Verify Network.';
                        } else if (jqXHR.status == 404) {
                            msg = 'Requested page not found. [404]';
                        } else if (jqXHR.status == 500) {
                            msg = 'Internal Server Error [500].';
                        } else if (exception === 'parsererror') {
                            msg = 'Requested JSON parse failed.';
                        } else if (exception === 'timeout') {
                            msg = 'Time out error.';
                        } else if (exception === 'abort') {
                            msg = 'Ajax request aborted.';
                        } else {
                            msg = 'Uncaught Error.\n' + jqXHR.responseText;
                        }

                        alert(msg);
                    }
                });
            });

            function countLimits() {
                let material_name = [];
                let limit_name = [];
                let qty = 0;
                let limit_qty = 0;
                let name = null;
                let match = null;
                let data = {};
                const amountRegex = /(\d+)\s+шт/g;
                const materialRegex = /^\s*(.+?)\s*-\s*\d+\s*шт\./;
                let matchAmountRegex;
                let matchMaterialRegex;
                let dataInStock = [];
                let stock;
                let ci_flow = $("#ci_flow").val()

                /*
                    TODO Хардкод, его я сам написал, просто быстрое решение, подумай как лучше получить данные сюда.
                        В целом, функционал я написал по требованию, но как таковой по фронту это невозможно,
                        т.к при данных типах услуг не доступен интерфейс списания ТМЦ.
                */
                let require_ci_flow = [
                    'Блокировка услуг с выездом (Суз Портал)',
                    'Блокировка услуг (задолженность) с выездом (Суз Портал)',
                    'Разблокировка услуг с выездом (Суз Портал)',
                    '(ПОТОМОК) Отключение услуг по старому адресу (Суз Портал)'
                ];

                let confirm_message = '';

                $('.materials-div tr').each(function () {
                    $('.sub-in-stock li').each(function () {
                        let text = $(this).text().trim();

                        matchAmountRegex = amountRegex.exec(text);
                        matchMaterialRegex = text.match(materialRegex);

                        if (matchAmountRegex && matchMaterialRegex && matchMaterialRegex[1]) {
                            dataInStock.push({
                                'material': matchMaterialRegex[1],
                                'amountInStock': matchAmountRegex[1],
                                'binded': $(this).data('binded') || 0
                            })
                        }
                    });

                    qty = $(this).data('qty');
                    if (qty !== undefined) {

                        limit_qty = $(this).data('limit_qty');
                        name = $(this).data('name');

                        data = {
                            "name": name,
                            "limit": limit_qty,
                            "qty": qty
                        }

                        match = material_name.filter(obj => obj.name === name);
                        material_name.push(data);

                        if (match) {
                            qty += match.reduce((total, obj) => total + obj.qty, 0);

                        }

                        stock = dataInStock.find(obj => obj.material === name)

                        if (stock) {
                            qty += stock.binded
                        }

                        if (limit_qty !== null && qty > limit_qty && !limit_name.find(obj => obj.name === name)) {
                            limit_name.push(data);
                        }
                    }
                });

                //TODO Вывести в отдельную функцию
                if (limit_name.length !== 0 || (ci_flow && require_ci_flow.includes(ci_flow))) {

                    if (limit_name.length) {
                        let names = limit_name.map(function (obj) {
                            return obj.name;
                        });
                        confirm_message = 'На данные ТМЦ превышен лимит: ' + names.join(', ') + ' Все равно записать?';
                    } else {
                        confirm_message = 'На данный тип заказа, стоит ограничение по списанию. Все равно записать? ';
                    }

                    if (confirm(confirm_message) === false) {
                        return false;
                    } else {
                        $('input[name=limit_input]').val('true')
                    }
                }

                return true;
            }

            async function getLogin() {
                // let idHouse = 35297;
                // let osn = 'HWTC6ABB1CCC';

                let idHouse = $('#getGponLogin').data('id-house');
                let osn = [];

                var rows = document.getElementsByClassName("equipment-row direction-give");

                for (var i = 0; i < rows.length; i++) {
                    var tds = rows[i].getElementsByTagName("td");
                    var lastValue = tds[tds.length - 1].textContent.trim();
                    osn.push(lastValue);
                }

                try {
                    return await $.ajax({
                        url: "/getJponLogin/" + idHouse,
                        type: "POST",  // Use POST instead of GET for sending JSON data in the body
                        headers: {
                            "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                        },
                        data: JSON.stringify({
                            osn: osn
                        }),
                        contentType: "application/json",
                    });
                } catch (error) {
                    let msg = '';

                    if (error.status === 0) {
                        msg = 'Not connect.\nVerify Network.';
                    } else if (error.status === 404) {
                        msg = 'Requested page not found. [404]';
                    } else if (error.status === 500) {
                        msg = 'Internal Server Error [500].';
                    } else if (error.statusText === 'parsererror') {
                        msg = 'Requested JSON parse failed.';
                    } else if (error.statusText === 'timeout') {
                        msg = 'Time out error.';
                    } else if (error.statusText === 'abort') {
                        msg = 'Ajax request aborted.';
                    } else {
                        msg = 'Uncaught Error.\n' + error.responseText;
                    }

                    console.error(error);  // Log the error
                    throw new Error(msg);
                }
            }

            if(document.getElementById('getGponLogin')) {
                document.getElementById('getGponLogin').addEventListener('click', () => {

                    document.getElementById('getGponLogin').disabled = true;

                    getLogin()
                        .then(response => {
                            let parsedResponse = JSON.parse(response.response);

                            if(parsedResponse.code == 200) {
                                $('#v_param_internet').empty();
                                $('#v_param_internet').val(parsedResponse.data);
                            } else {
                                alert(parsedResponse.data);
                            }
                        })
                        .catch(error => {
                            alert('Произошла ошибка!');
                            // Handle the error here
                        }).finally(() => {
                            document.getElementById('getGponLogin').disabled = false;
                        });
                });
            }
        });

    </script>
@endsection
