@extends('layouts.app')

@php
    $pageTitle = 'Инвентарь';
    $selectedStock = isset($_GET['stock']) ? $_GET['stock'] : '';
    $selectedMnemonic = isset($_GET['mnemonic']) ? $_GET['mnemonic'] : '';
@endphp

@section('pageTitle', $pageTitle)

@section('content')
    <div class="container-fluid mt--7">
        <div class="card min-height-500">
            <div class="card-header">
                @if(session('message'))
                    <div class="my-4 alert alert-{{ session('success') ? 'success' : 'danger' }} alert-temporary">{{ is_array(session('message')) ? explode('<br>', session('message')) : session('message') }}</div>
                @endif
                <div class="row">
                    <div class="col-md-8">
                        <h1>{{ $pageTitle }}</h1>
                        @if(\Auth::user()->locations->count() > 0)
                            <form action="" class="d-block" style="width:100%;">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="stock">Склады:</label>
                                            <select name="stock" id="stock" class="form-control" onchange="this.form.submit()">
                                                <option selected disabled>Выберите склад</option>
                                                @foreach($stocks as $stock)
                                                    <option value="{{ $stock->id }}" {{ $stock->id == $selectedStock ? 'selected' : '' }}>{{ $stock->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3 filter-types">
                                        <div class="form-group">
                                            <label for="mnemonic">Тип:</label>
                                            <select name="mnemonic" id="mnemonic" class="form-control" onchange="this.form.submit()">
                                                <option value="all">Все</option>
                                                @foreach($mnemonics as $mnemonic)
                                                    <option value="{{ $mnemonic->v_mnemonic }}" {{ $mnemonic->v_mnemonic == $selectedMnemonic ? 'selected' : '' }}>{{ $mnemonic->v_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <!-- <div class="col-md-2">
                                        <label>&nbsp;</label>
                                        <button class="d-block btn btn-primary">Применить</button>
                                    </div> -->
                                </div>
                            </form>
                        @else
                            <h5 class="font-weight-400">Не привязан к участку</h5>
                        @endif
                    </div>
                    <div class="col-md-4 text-right">
                        <a href="/inventory/movement-story?stock_id={{ $selectedStock }}" class="btn btn-info" style="margin-right .5rem" id="movement_story_btn"><i class="fas fa-list-alt"></i> История перемещений</a>
                    @if($showUploadMaterialsBtn)
                        <a href="/materials/upload" class="btn btn-white"><i class="fas fa-cloud-upload-alt"></i> Загрузить расходники</a>
                    @endif
                        <button class="btn btn-white" id="massModal_btn"><i class="far fa-list-alt"></i> Массовое перемещение</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                @if(\Auth::user()->stocks->count() > 0)
                <div class="row min-height-md-500 py-3">
                    <div class="col-3">
                        <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                            <a class="mb-3 nav-link active" id="v-pills-main-tab" data-toggle="pill" href="#v-pills-main" role="tab" aria-controls="v-pills-main" aria-selected="true"><i class="fas fa-folder"></i> Комплекты</a>
                            <a class="mb-3 nav-link" id="v-pills-kit-request-2-tab" data-toggle="pill" href="#v-pills-kit-request-2" role="tab" aria-controls="v-pills-kit-request-2" aria-selected="false"><i class="fas fa-folder"></i> Комплекты (принимаемые)</a>
                            <a class="mb-3 nav-link" id="v-pills-kit-request-1-tab" data-toggle="pill" href="#v-pills-kit-request-1" role="tab" aria-controls="v-pills-kit-request-1" aria-selected="false"><i class="fas fa-folder"></i> Комплекты (отправленные)</a>
                            <!-- <a class="mb-3 nav-link" id="v-pills-equipments-tab" data-toggle="pill" href="#v-pills-equipments" role="tab" aria-controls="v-pills-equipments" aria-selected="false"><i class="fas fa-folder"></i> Комплектующие</a> -->
                            <a class="mb-3 nav-link" id="v-pills-materials-tab" data-toggle="pill" href="#v-pills-materials" role="tab" aria-controls="v-pills-materials" aria-selected="false"><i class="fas fa-folder"></i> Расходные</a>
                            <a class="mb-3 nav-link" id="v-pills-materials-request-2-tab" data-toggle="pill" href="#v-pills-materials-request-2" role="tab" aria-controls="v-pills-materials-request-2" aria-selected="false"><i class="fas fa-folder"></i> Расходные (принимаемые)</a>
                            <a class="mb-3 nav-link" id="v-pills-materials-request-1-tab" data-toggle="pill" href="#v-pills-materials-request-1" role="tab" aria-controls="v-pills-materials-request-1" aria-selected="false"><i class="fas fa-folder"></i> Расходные (отправленные)</a>
                        </div>
                    </div>
                    <div class="col-9 border-left">
                        <div class="tab-content" id="v-pills-tabContent">
                            <div class="tab-pane fade show active" id="v-pills-main" role="tabpanel" aria-labelledby="v-pills-main-tab">
                                @if($kits && $kits->count() > 0)
                                    <div class="border-top py-4 d-flex flex-row justify-content-between">
                                        <div class="h2 py-2 my-auto">
                                            Комплекты
                                        </div>
                                        <div class="h4 py-2 float-right">
                                            @php
                                                $links_json = json_decode($kits->toJson());
                                            @endphp
                                            {{ $links_json->from }}–{{ $links_json->to }} из {{ $links_json->total }}
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <th><input type="checkbox" id="check_all_kits"></th>
                                                <th>Название</th>
                                                <th>Серийный номер</th>
                                                <th>Дата последней передачи</th>
                                                <th>Демонтаж?</th>
                                                <th></th>
                                            </thead>
                                            <tbody>
                                            @foreach($kits as $key => $kit)
                                                <tr>
                                                    <td><input type="checkbox" name="kits[]" value="{{ $kit->id }}"></td>
                                                    <td>
                                                        <button class="btn btn-sm btn-secondary align-initial show-equipment-btn mr-1"
                                                                data-id="{{ $kit->id }}"><i class="fas fa-list-ul"></i></button>
                                                        <span class="h4 kit-name">{{ $kit->name }}</span>
                                                    </td>
                                                    <td class="v-serial"><code class="h3 font-weight-bold text-red">{{ $kit->v_serial }}</code></td>
                                                    <td>{{ $kit->updated_at }}</td>
                                                    <td>{!! $kit->returned ? '<i class="fas fa-check text-success"></i>' : 'Нет' !!}</td>
                                                    <td><a href="/kit/{{ $kit->id }}" target="_blank" class="btn btn-success btn-sm">История</a></td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="my-4 border-top border-bottom py-4">
                                        <div class="row align-items-center">
                                            <div class="col-md-8 mt-2">
                                                {{ $kits->appends(request()->query())->links() }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="my-4">
                                        <button class="btn btn-success" id="issue_all_kit_btn" disabled><i class="fas fa-hand-point-right"></i> Выдать</button>
                                        <button class="btn btn-warning" id="move_all_kit_btn" disabled><i class="fas fa-share"></i> Переместить</button>
                                    </div>
                                @endif
                            </div>
                            <div class="tab-pane fade" id="v-pills-equipments" role="tabpanel" aria-labelledby="v-pills-equipments">
                                @if($equipments && $equipments->count() > 0)
                                    <div class="border-top py-4 d-flex flex-row justify-content-between">
                                        <div class="h2 py-2 my-auto">
                                            Комплектующие
                                        </div>
                                        <div class="h4 py-2 float-right">
                                            @php
                                                $links_json = json_decode($kits->toJson());
                                            @endphp
                                            {{ $links_json->from }}–{{ $links_json->to }} из {{ $links_json->total }}
                                        </div>
                                    </div>
                                    <table class="table table-hover">
                                        <thead>
                                            <th><input type="checkbox" id="check_all_equipments"></th>
                                            <th>Название</th>
                                            <th>Вендор</th>
                                            <th>Серийный номер оборудования</th>
                                            <th>Серийный номер комплекта</th>
                                            <th></th>
                                        </thead>
                                        <tbody>
                                        @foreach($equipments as $eq)
                                            <tr>
                                                <td><input type="checkbox" name="equipments[]" value="{{ $eq->id }}"></td>
                                                <td>
                                                    <span class="h4 equipment-name">{{ $eq->model->v_name ?? 'Модель неизвестна' }}</span>
                                                </td>
                                                <td>{{ $eq->model->v_vendor ?? 'Вендор неизвестен' }}</td>
                                                <td class="v-serial"><code class="h4 font-weight-bold text-red">{{ $eq->kit->v_serial ?? '-' }}</code></td>
                                                <td><code class="h4 font-weight-bold text-primary">{{ $eq->v_equipment_number }}</code></td>
                                                <td><a href="/equipment/{{ $eq->id }}" target="_blank" class="btn btn-success btn-sm">История</a></td>
                                            </tr>
                                        @endforeach
                                        </tbody>
                                    </table>
                                    <div class="my-4 border-top border-bottom py-4">
                                        <div class="row align-items-center">
                                            <div class="col-md-8 mt-2">
                                                {{ $equipments->appends(request()->query())->links() }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="my-4">
                                        <button class="btn btn-warning" id="move_all_equipments_btn" disabled><i class="fas fa-share"></i> Переместить</button>
                                    </div>
                                @endif
                            </div>
                            <div class="tab-pane fade" id="v-pills-materials" role="tabpanel" aria-labelledby="v-pills-materials-tab">
                                @if($materials)
                                    @if($materials->count() > 0)
                                    <div class="border-top py-4 d-flex flex-row justify-content-between">
                                        <div class="h2 py-2 my-auto">
                                            Расходные материалы
                                        </div>
                                        <div class="h4 py-2 float-right">
                                            @php
                                                $links_json = json_decode($materials->toJson());
                                            @endphp
                                            {{ $links_json->from }}–{{ $links_json->to }} из {{ $links_json->total }}
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <th><input type="checkbox" id="check_all_materials"></th>
                                                <th>Название</th>
                                                <th>Тип</th>
                                                <th>Количество</th>
                                                <th></th>
                                            </thead>
                                            <tbody>
                                                @foreach($materials as $m)
                                                    <tr>
                                                        <td><input type="checkbox" name="materials[]" data-max_qty="{{ $m->qty }}" data-name="{{ $m->name }}" value="{{ $m->material_id }}"></td>
                                                        <td><span class="text-dark font-weight-bold">{{ $m->name }}</span></td>
                                                        <td><b>{{ $m->type }}</b></td>
                                                        <td>{{ $m->qty }} шт.</td>
                                                        <td><a href="/material/{{ $m->material_id }}" class="btn btn-success btn-sm">Перейти</a></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        <div class="my-4 border-top border-bottom py-4">
                                            <div class="row align-items-center">
                                                <div class="col-md-8 mt-2">
                                                    {{ $materials->appends(request()->query())->links() }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="my-4">
                                            <button class="btn btn-success" id="issue_all_materials_btn" disabled><i class="fas fa-hand-point-right"></i> Выдать</button>
                                            <button class="btn btn-warning" id="move_all_materials_btn" disabled><i class="fas fa-share"></i> Переместить</button>
                                        </div>
                                    </div>
                                    @else
                                        <div class="text-center border-top py-5">Нет расходных материалов</div>
                                    @endif
                                @else
                                    <div class="text-center border-top py-5">Выберите склад</div>
                                @endif
                            </div>
                            <div class="tab-pane fade" id="v-pills-kit-request-1" role="tabpanel" aria-labelledby="v-pills-kit-request-1-tab">
                                 @if($kit_request_from)
                                    @if($kit_request_from->count() > 0)
                                    <div class="border-top py-4 d-flex flex-row justify-content-between">
                                        <div class="h2 py-2 my-auto">
                                            Отправленные комплекты
                                        </div>
                                        <div class="h4 py-2 float-right">
                                            @php
                                                $links_json = json_decode($kit_request_from->toJson());
                                            @endphp
                                            {{ $links_json->from }}–{{ $links_json->to }} из {{ $links_json->total }}
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <th>Название</th>
                                                <th>Серийный номер</th>
                                                <th>Куда отправлено</th>
                                                <th>Дата перемещения</th>
                                            </thead>
                                            <tbody>
                                                @foreach($kit_request_from as $kr)
                                                    <tr>
                                                        <td>
                                                            <button class="btn btn-sm btn-secondary align-initial show-equipment-btn mr-1"
                                                                data-id="{{ $kr->kit_id }}"><i class="fas fa-list-ul"></i></button>
                                                            @if(isset($kr->name))
                                                            <span class="h4 kit-name">{{ $kr->name }}</span>
                                                            @else
                                                            <span class="h4 kit-name">{{ $kr->kit_id }}</span>
                                                            @endif
                                                        </td>
                                                        <td class="v-serial"><code class="h3 font-weight-bold text-red">{{ $kr->v_serial }}</code></td>
                                                        @if(is_null($kr->user_id))
                                                        <td>{{ $kr->stockname }}</td>
                                                        @else
                                                        <td>{{ $kr->username }}</td>
                                                        @endif
                                                        <td>{{ $kr->created_at }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        <div class="my-4 border-top border-bottom py-4">
                                            <div class="row align-items-center">
                                                <div class="col-md-8 mt-2">
                                                    {{ $kit_request_from->appends(request()->query())->links() }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @else
                                        <div class="text-center border-top py-5">Нет комплектов</div>
                                    @endif
                                @else
                                    <div class="text-center border-top py-5">Выберите склад</div>
                                @endif
                            </div>
                            <div class="tab-pane fade" id="v-pills-kit-request-2" role="tabpanel" aria-labelledby="v-pills-kit-request-2-tab">
                                 @if($kit_request_to)
                                    @if($kit_request_to->count() > 0)
                                    <div class="border-top py-4 d-flex flex-row justify-content-between">
                                        <div class="h2 py-2 my-auto">
                                            Ожидающие принятия комплекты
                                        </div>
                                        <div class="h4 py-2 float-right">
                                            @php
                                                $links_json = json_decode($kit_request_to->toJson());
                                            @endphp
                                            {{ $links_json->from }}–{{ $links_json->to }} из {{ $links_json->total }}
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <th><input type="checkbox" id="check_all_kit_request"></th>
                                                <th>Название</th>
                                                <th>Серийный номер</th>
                                                <th>Откуда отправлено</th>
                                                <th>Дата перемещения</th>
                                            </thead>
                                            <tbody>
                                                @foreach($kit_request_to as $kr)
                                                    <tr>
                                                        <td><input type="checkbox" name="kit_request[]" value="{{ $kr->id }}"></td>
                                                        <td>
                                                            <button class="btn btn-sm btn-secondary align-initial show-equipment-btn mr-1"
                                                                data-id="{{ $kr->kit_id }}"><i class="fas fa-list-ul"></i></button>
                                                            @if(isset($kr->name))
                                                            <span class="h4 kit-name">{{ $kr->name }}</span>
                                                            @else
                                                            <span class="h4 kit-name">{{ $kr->kit_id }}</span>
                                                            @endif
                                                        </td>
                                                        <td class="v-serial"><code class="h3 font-weight-bold text-red">{{ $kr->v_serial }}</code></td>
                                                        @if(is_null($kr->user_id))
                                                        <td>{{ $kr->from_stockname }}</td>
                                                        @else
                                                        <td>{{ $kr->username }}</td>
                                                        @endif
                                                        <td>{{ $kr->created_at }}</td>
                                                        <!-- <td>
                                                            <button class="btn btn-success kit-get-btn" data-id="{{ $kr->id }}">Принять</button>
                                                            <button class="btn btn-danger kit-cancel-btn" data-id="{{ $kr->id }}">Отклонить</button>
                                                        </td> -->
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        <div class="my-4 border-top border-bottom py-4">
                                            <div class="row align-items-center">
                                                <div class="col-md-8 mt-2">
                                                    {{ $kit_request_to->appends(request()->query())->links() }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="my-4">
                                            <button class="btn btn-success" id="get_all_kits_btn" disabled><i class="fas fa-hand-point-right"></i> Принять</button>
                                            <button class="btn btn-danger" id="cancel_all_kits_btn" disabled><i class="fas fa-share"></i> Отклонить</button>
                                        </div>
                                    </div>
                                    @else
                                        <div class="text-center border-top py-5">Нет комплектов</div>
                                    @endif
                                @else
                                    <div class="text-center border-top py-5">Выберите склад</div>
                                @endif
                            </div>
                            <div class="tab-pane fade" id="v-pills-materials-request-1" role="tabpanel" aria-labelledby="v-pills-materials-request-1-tab">
                                 @if($materials_request_from)
                                    @if($materials_request_from->count() > 0)
                                    <div class="border-top py-4 d-flex flex-row justify-content-between">
                                        <div class="h2 py-2 my-auto">
                                            Отправленные расходные материалы
                                        </div>
                                        <div class="h4 py-2 float-right">
                                            @php
                                                $links_json = json_decode($materials_request_from->toJson());
                                            @endphp
                                            {{ $links_json->from }}–{{ $links_json->to }} из {{ $links_json->total }}
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <th>Название</th>
                                                <th>Тип</th>
                                                <th>Количество</th>
                                                <th>Куда отправлено</th>
                                                <th>Дата перемещения</th>
                                            </thead>
                                            <tbody>
                                                @foreach($materials_request_from as $mr)
                                                    <tr>
                                                        <td><span class="text-dark font-weight-bold">{{ $mr->name }}</span></td>
                                                        <td><b>{{ $mr->type }}</b></td>
                                                        <td>{{ $mr->qty }} шт.</td>
                                                        @if(is_null($mr->user_id))
                                                        <td>{{ $mr->stockname }}</td>
                                                        @else
                                                        <td>{{ $mr->username }}</td>
                                                        @endif
                                                        <td>{{ $mr->created_at }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        <div class="my-4 border-top border-bottom py-4">
                                            <div class="row align-items-center">
                                                <div class="col-md-8 mt-2">
                                                    {{ $materials_request_from->appends(request()->query())->links() }}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @else
                                        <div class="text-center border-top py-5">Нет расходных материалов</div>
                                    @endif
                                @else
                                    <div class="text-center border-top py-5">Выберите склад</div>
                                @endif
                            </div>
                            <div class="tab-pane fade" id="v-pills-materials-request-2" role="tabpanel" aria-labelledby="v-pills-materials-request-2-tab">
                                @if($materials_request_to)
                                    @if($materials_request_to->count() > 0)
                                    <div class="border-top py-4 d-flex flex-row justify-content-between">
                                        <div class="h2 py-2 my-auto">
                                            Ожидающие принятия расходные материалы
                                        </div>
                                        <div class="h4 py-2 float-right">
                                            @php
                                                $links_json = json_decode($materials_request_to->toJson());
                                            @endphp
                                            {{ $links_json->from }}–{{ $links_json->to }} из {{ $links_json->total }}
                                        </div>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover">
                                            <thead>
                                                <th><input type="checkbox" id="check_all_materials_request"></th>
                                                <th>Название</th>
                                                <th>Тип</th>
                                                <th>Количество</th>
                                                <th>Откуда отправлено</th>
                                                <th>Дата перемещения</th>
                                            </thead>
                                            <tbody>
                                                @foreach($materials_request_to as $mr)
                                                    <tr>
                                                        <td><input type="checkbox" name="materials_request[]" value="{{ $mr->id }}"></td>
                                                        <td><span class="text-dark font-weight-bold">{{ $mr->name }}</span></td>
                                                        <td><b>{{ $mr->type }}</b></td>
                                                        <td>{{ $mr->qty }} шт.</td>
                                                        @if(is_null($mr->user_id))
                                                        <td>{{ $mr->from_stockname }}</td>
                                                        @else
                                                        <td>{{ $mr->username }}</td>
                                                        @endif
                                                        <td>{{ $mr->created_at }}</td>
                                                        <!-- <td>
                                                            <button class="btn btn-success materials-get-btn" data-id="{{ $mr->id }}">Принять</button>
                                                            <button class="btn btn-danger materials-cancel-btn" data-id="{{ $mr->id }}">Отклонить</button>
                                                        </td> -->
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                        <div class="my-4 border-top border-bottom py-4">
                                            <div class="row align-items-center">
                                                <div class="col-md-8 mt-2">
                                                    {{ $materials_request_to->appends(request()->query())->links() }}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="my-4">
                                            <button class="btn btn-success" id="get_all_materials_btn" disabled><i class="fas fa-hand-point-right"></i> Принять</button>
                                            <button class="btn btn-danger" id="cancel_all_materials_btn" disabled><i class="fas fa-share"></i> Отклонить</button>
                                        </div>
                                    </div>
                                    @else
                                        <div class="text-center border-top py-5">Нет расходных материалов</div>
                                    @endif
                                @else
                                    <div class="text-center border-top py-5">Выберите склад</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
                @else
                <div class="h5 text-center py-4">Вы не привязаны ни к одному складу</div>
                @endif
            </div>
        </div>
    </div>
    <!-- List Equipment Modal -->
    <div class="modal fade" id="equipmentListModal" tabindex="-1" role="dialog" aria-labelledby="equipmentListModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document" style="min-width: 1100px;">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="equipmentListModalLabel">Экземпляры на складе</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Issue Kit Modal -->
    <div class="modal fade" id="issueModal" tabindex="-1" role="dialog" aria-labelledby="issueModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h4 class="modal-title mb-0" id="issueModalLabel">Выдача оборудования</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('issue_kit') }}" method="post">
                    @csrf
                    <input type="hidden" name="kit_id">
                    <div class="modal-body">
                        <div class="h4 mb-2 text-center modal-kit-name"></div>
                        <div class="h5 text-red text-center modal-kit-serial"></div>
                        <div class="form-group">
                            <label for="whom">Кому выдать:</label>
                            @if($installers && count($installers) > 0)
                            <select name="whom" id="whom" class="form-control" required>
                                @foreach($installers as $whom)
                                    <option value="{{ $whom->id }}">{{ $whom->name }}</option>
                                @endforeach
                            </select>
                            @else
                                <div class="my-2 alert alert-danger">Нет монтажников</div>
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer">
                        @if($installers && count($installers) > 0)
                        <button type="submit" class="btn btn-success">Выдать</button>
                        @endif
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Move Kit Modal -->
    <div class="modal fade" id="moveKitModal" tabindex="-1" role="dialog" aria-labelledby="moveKitModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h4 class="modal-title mb-0" id="moveKitModalLabel">Передача комплекта</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('move_kit') }}" method="post">
                    @csrf
                    <input type="hidden" name="kit_id">
                    <div class="modal-body">
                        <div class="h4 mb-2 text-center modal-kit-name"></div>
                        <div class="h5 text-red text-center modal-kit-serial"></div>
                        <div class="form-group">
                            <label for="stock">Выберите склад:</label>
                            <select name="stock" id="stock" class="form-control">
                                @if($stocks)
                                    @foreach($stocks as $stock)
                                        @if($stock->id != $selectedStock)
                                        <option value="{{ $stock->id }}">{{ $stock->name }}</option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Переместить</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Move Equipment Modal -->
    <div class="modal fade" id="moveEquipmentModal" tabindex="-1" role="dialog" aria-labelledby="moveEquipmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h4 class="modal-title mb-0" id="moveEquipmentModalLabel">Передача оборудования</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('move_equipment') }}" method="post">
                    @csrf
                    <input type="hidden" name="equipment_ids">
                    <div class="modal-body">
                        <div class="h4 mb-2 text-center modal-equipment-name"></div>
                        <div class="h5 text-red text-center modal-equipment-serial"></div>
                        <div class="form-group">
                            <label for="stock">Выберите склад:</label>
                            <select name="stock" id="stock" class="form-control">
                                @if($stocks)
                                    @foreach($stocks as $stock)
                                        @if($stock->id != $selectedStock && $stock->return == 1)
                                        <option value="{{ $stock->id }}">{{ $stock->name }}</option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Передать</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Move Materials Modal -->
    <div class="modal fade" id="moveMaterialsModal" tabindex="-1" role="dialog" aria-labelledby="moveMaterialsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h4 class="modal-title mb-0" id="moveMaterialsModalLabel">Передача расходников</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('move_materials') }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="h4 mb-2 text-center modal-equipment-name"></div>
                        <div class="h5 text-red text-center modal-equipment-serial"></div>
                        <div class="form-group">
                            <label for="stock">Выберите склад:</label>
                            <select name="stock" id="stock" class="form-control">
                                @if($stocks)
                                    @foreach($stocks as $stock)
                                        @if($stock->id != $selectedStock)
                                        <option value="{{ $stock->id }}">{{ $stock->name }}</option>
                                        @endif
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="materials"></div>
                        <input type="hidden" name="materials">
                        <input type="hidden" name="stock_id">
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Передать</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Mass Modal -->
    <div class="modal fade" id="massModal" tabindex="-1" role="dialog" aria-labelledby="massModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xlg" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h4 class="modal-title mb-0" id="massModalLabel">Перемещение/выдача оборудования списком</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4">
                            <label class="h4">Серийные номера:</label>
                            <textarea id="mass_modal_equipment_numbers" cols="1" rows="10" class="form-control" placeholder="Вставьте серийные номера..."></textarea>
                            <button class="btn btn-white mt-2 w-100" id="mass_modal_search_btn">Поиск</button>
                        </div>
                        <div class="col-md-8">
                            <h4>Результат поиска:</h4>
                            <div id="mass_modal_result" style="max-height:600px;overflow-y:auto;"></div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button class="btn btn-success" id="mass_modal_issue_btn">Выдать</button>
                    <button class="btn btn-success" id="mass_modal_move_btn">Переместить</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Issue Materials Modal -->
    <div class="modal fade" id="issueMaterialsModal" tabindex="-1" role="dialog" aria-labelledby="issueMaterialsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header border-bottom">
                    <h4 class="modal-title mb-0" id="issueMaterialsModalLabel">Выдача расходников</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('issue_materials') }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="whom">Кому выдать:</label>
                            @if($installers && count($installers) > 0)
                                <select name="whom" id="whom" class="form-control" required>
                                    @foreach($installers as $whom)
                                        <option value="{{ $whom->id }}">{{ $whom->name }}</option>
                                    @endforeach
                                </select>
                            @else
                                <div class="my-2 alert alert-danger">Нет монтажников</div>
                            @endif
                        </div>
                        <div class="materials"></div>
                        <input type="hidden" name="materials">
                        <input type="hidden" name="stock_id">
                    </div>
                    <div class="modal-footer">
                        @if($installers && $installers->count() > 0)
                        <button type="submit" class="btn btn-success">Выдать</button>
                        @endif
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@endsection

@section('page-scripts')
    <script type="text/javascript">
        $(document).ready(function(){
            $(".kit-get-btn").on("click", function(){
                let id = $(this).data('id');
                if(confirm('Вы действительно хотите принять этот комплект?')){
                    $.ajax({
                        url: '/kit_request_get',
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
            $(".kit-cancel-btn").on("click", function(){
                let id = $(this).data('id');
                if(confirm('Вы действительно хотите отклонить этот комплект?')){
                    $.ajax({
                        url: '/kit_request_delete',
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
            $(".materials-get-btn").on("click", function(){
                let id = $(this).data('id');
                if(confirm('Вы действительно хотите принять расходники?')){
                    $.ajax({
                        url: '/materials_request_get',
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
            $(".materials-cancel-btn").on("click", function(){
                let id = $(this).data('id');
                if(confirm('Вы действительно хотите отклонить расходники?')){
                    $.ajax({
                        url: '/materials_request_delete',
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
            $('.show-equipment-btn').on('click', function(){
                let kitId = $(this).data('id');
                $.ajax({
                    url: '/getEquipmentList',
                    data: {
                        kit_id: kitId
                    },
                    dataType: 'json',
                    success(response){
                        let modal = $('#equipmentListModal');
                        modal.find('.modal-body').html(response['html']);
                        modal.modal('show');
                    }
                });
            });
            // --------------------------------------------

            $('#get_all_kits_btn').on('click', function(){
                let kit_ids = [];
                $("input[name='kit_request[]']:checked").each(function(){
                    kit_ids.push($(this).val());
                });
                if(confirm('Вы действительно хотите принять выбранные комплекты?')){
                    $.ajax({
                        url: '/kit_request_get',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            _token: $("input[name='_token']").val(),
                            id: kit_ids
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

            $('#cancel_all_kits_btn').on('click', function(){
                let kit_ids = [];
                $("input[name='kit_request[]']:checked").each(function(){
                    kit_ids.push($(this).val());
                });
                if(confirm('Вы действительно хотите отклонить выбранные комплекты?')){
                    $.ajax({
                        url: '/kit_request_delete',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            _token: $("input[name='_token']").val(),
                            id: kit_ids
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

            $('#get_all_materials_btn').on('click', function(){
                let materials_ids = [];
                $("input[name='materials_request[]']:checked").each(function(){
                    materials_ids.push($(this).val());
                });
                if(confirm('Вы действительно хотите принять выбранные расходники?')){
                    $.ajax({
                        url: '/materials_request_get',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            _token: $("input[name='_token']").val(),
                            id: materials_ids
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

            $('#cancel_all_materials_btn').on('click', function(){
                let materials_ids = [];
                $("input[name='materials_request[]']:checked").each(function(){
                    materials_ids.push($(this).val());
                });
                if(confirm('Вы действительно хотите отклонить выбранные расходники?')){
                    $.ajax({
                        url: '/materials_request_delete',
                        type: 'POST',
                        dataType: 'json',
                        data: {
                            _token: $("input[name='_token']").val(),
                            id: materials_ids
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

            // General kits
            $('#issue_all_kit_btn').on('click', function(){
                let kit_ids = [];
                $("input[name='kits[]']:checked").each(function(){
                    kit_ids.push($(this).val());
                });
                let modal = $('#issueModal');
                modal.find('input[name="kit_id"]').val(kit_ids);
                modal.modal('show');
            });

            // Mass issue
            $('#mass_modal_issue_btn').on('click', function(){
                if($('body').find('#mass_modal_clean').val() == 1){
                    let kit_ids = $('#mass_modal_kit_ids').val();
                    let modal = $('#issueModal');
                    modal.find('input[name="kit_id"]').val(kit_ids);
                    $('#massModal').modal('hide');
                    modal.modal('show');
                }else{
                    alert('Некоторое оборудование не может быть выдано!');
                }
            });

            // Mass move
            $('#mass_modal_move_btn').on('click', function(){
                if($('body').find('#mass_modal_clean').val() == 1){
                    let kit_ids = $('#mass_modal_kit_ids').val();
                    let modal = $('#moveKitModal');
                    modal.find('input[name="kit_id"]').val(kit_ids);
                    $('#massModal').modal('hide');
                    modal.modal('show');
                }else{
                    alert('Некоторое оборудование не может быть выдано!');
                }
            });

            $('#move_all_kit_btn').on('click', function(){
                let kit_ids = [];
                $("input[name='kits[]']:checked").each(function(){
                    kit_ids.push($(this).val());
                });
                let modal = $('#moveKitModal');
                modal.find('input[name="kit_id"]').val(kit_ids);
                modal.modal('show');
            });

            // -------------------------------------------
            // Materials
            $('#issue_all_materials_btn').on('click', function(){
                let materials = [];
                let stock_id = $("#stock").val();
                let html = "";
                $("input[name='materials[]']:checked").each(function(){
                    materials.push($(this).val());
                    let max = $(this).data('max_qty');
                    html += "<div class='form-group row'><label class='col-md-9 col-form-label'>" + $(this).data('name') + "</label>";
                    html += "<div class='col-md-3'><input type='number' placeholder='Кол-во' min='1' max='" + max + "' step='1' class='form-control' name='qty[]'></div></div>";
                });
                let modal = $('#issueMaterialsModal');
                modal.find('.materials').html(html);
                modal.find('input[name="materials"]').val(materials);
                modal.find('input[name="stock_id"]').val(stock_id);
                modal.modal('show');
            });
            $('#move_all_materials_btn').on('click', function(){
                let materials = [];
                let stock_id = $("#stock").val();
                let html = "";
                $("input[name='materials[]']:checked").each(function(){
                    materials.push($(this).val());
                    let max = $(this).data('max_qty');
                    html += "<div class='form-group row'><label class='col-md-9 col-form-label'>" + $(this).data('name') + "</label>";
                    html += "<div class='col-md-3'><input type='number' placeholder='Кол-во' min='1' max='" + max + "' step='1' class='form-control' name='qty[]'></div></div>";
                });
                let modal = $('#moveMaterialsModal');
                modal.find('.materials').html(html);
                modal.find('input[name="materials"]').val(materials);
                modal.find('input[name="stock_id"]').val(stock_id);
                modal.modal('show');
            });

            // --------------------------------------------
            // Equipments
            $('#issue_all_equipments_btn').on('click', function(){
                let ids = [];
                $("input[name='equipments[]']:checked").each(function(){
                    ids.push($(this).val());
                });
                let modal = $('#issueModal');
                modal.find('input[name="equipment_ids"]').val(ids);
                modal.modal('show');
            });
            $('#move_all_equipments_btn').on('click', function(){
                let ids = [];
                $("input[name='equipments[]']:checked").each(function(){
                    ids.push($(this).val());
                });
                let modal = $('#moveEquipmentModal');
                modal.find('input[name="equipment_ids"]').val(ids);
                modal.modal('show');
            });
            // --------------------------------------------

            $("body").on("click", "#check_all_kit_request", function(){
                let checkBoxes = $("#v-pills-kit-request-2").find("input[name='kit_request[]']");
                checkBoxes.not(this).prop('checked', this.checked);
                toggleButtonsDisable('kit_request');
            });
            $("body").on("click", "#check_all_materials_request", function(){
                let checkBoxes = $("#v-pills-materials-request-2").find("input[name='materials_request[]']");
                checkBoxes.not(this).prop('checked', this.checked);
                toggleButtonsDisable('materials_request');
            });
            $("body").on("click", "#check_all_materials", function(){
                let checkBoxes = $("#v-pills-materials").find("input[name='materials[]']");
                checkBoxes.not(this).prop('checked', this.checked);
                toggleButtonsDisable('materials');
            });
            $("body").on("click", "#check_all_kits", function(){
                let checkBoxes = $("#v-pills-main").find("input[name='kits[]']");
                checkBoxes.not(this).prop('checked', this.checked);
                toggleButtonsDisable('main');
            });
            $("body").on("click", "#check_all_equipments", function(){
                let checkBoxes = $("#v-pills-equipments").find("input[name='equipments[]']");
                checkBoxes.not(this).prop('checked', this.checked);
                toggleButtonsDisable('equipments');
            });

            $("input[name='kits[]']").on('change', function(){
                toggleButtonsDisable('main');
            });
            $("input[name='equipments[]']").on("change", function(){
                toggleButtonsDisable('equipments');
            });
            $("input[name='materials[]']").on('change', function(){
                toggleButtonsDisable('materials');
            });
            $("input[name='kit_request[]']").on('change', function(){
                toggleButtonsDisable('kit_request');
            });
            $("input[name='materials_request[]']").on('change', function(){
                toggleButtonsDisable('materials_request');
            });
            var toggleButtonsDisable = function(type){
                if(type == 'main'){
                    if($("#v-pills-main").find("input[name='kits[]']:checked").length){
                        $("#issue_all_kit_btn").removeAttr('disabled');
                        $("#move_all_kit_btn").removeAttr('disabled');
                        $('.pagination').css({'pointer-events': 'none', 'opacity': '0.7'});
                    }else{
                        $("#issue_all_kit_btn").attr('disabled', 'disabled');
                        $("#move_all_kit_btn").attr('disabled', 'disabled');
                        $('.pagination').css({'pointer-events': 'initial', 'opacity': '1'});
                    }
                }else if(type == 'materials'){
                    if($("#v-pills-materials").find("input[name='materials[]']:checked").length){
                        $("#issue_all_materials_btn").removeAttr('disabled');
                        $("#move_all_materials_btn").removeAttr('disabled');
                        $("#v-pills-materials").find('.pagination').css({'pointer-events': 'none', 'opacity': '0.7'});
                    }else{
                        $("#issue_all_materials_btn").attr('disabled', 'disabled');
                        $("#move_all_materials_btn").attr('disabled', 'disabled');
                        $("#v-pills-materials").find('.pagination').css({'pointer-events': 'initial', 'opacity': '1'});
                    }
                }else if(type == 'kit_request'){
                    if($("#v-pills-kit-request-2").find("input[name='kit_request[]']:checked").length){
                        $("#get_all_kits_btn").removeAttr('disabled');
                        $("#cancel_all_kits_btn").removeAttr('disabled');
                        $("#v-pills-kit-request-2").find('.pagination').css({'pointer-events': 'none', 'opacity': '0.7'});
                    }else{
                        $("#get_all_kits_btn").attr('disabled', 'disabled');
                        $("#cancel_all_kits_btn").attr('disabled', 'disabled');
                        $("#v-pills-kit-request-2").find('.pagination').css({'pointer-events': 'initial', 'opacity': '1'});
                    }
                }else if(type == 'materials_request'){
                    if($("#v-pills-materials-request-2").find("input[name='materials_request[]']:checked").length){
                        $("#get_all_materials_btn").removeAttr('disabled');
                        $("#cancel_all_materials_btn").removeAttr('disabled');
                        $("#v-pills-materials-request-2").find('.pagination').css({'pointer-events': 'none', 'opacity': '0.7'});
                    }else{
                        $("#get_all_materials_btn").attr('disabled', 'disabled');
                        $("#cancel_all_materials_btn").attr('disabled', 'disabled');
                        $("#v-pills-materials-request-2").find('.pagination').css({'pointer-events': 'initial', 'opacity': '1'});
                    }
                }else{
                    if($("#v-pills-equipments").find("input[name='equipments[]']:checked").length){
                        $("#issue_all_equipments_btn").removeAttr('disabled');
                        $("#move_all_equipments_btn").removeAttr('disabled');
                        $("#v-pills-equipments").find('.pagination').css({'pointer-events': 'none', 'opacity': '0.7'});
                    }else{
                        $("#issue_all_equipments_btn").attr('disabled', 'disabled');
                        $("#move_all_equipments_btn").attr('disabled', 'disabled');
                        $("#v-pills-equipments").find('.pagination').css({'pointer-events': 'initial', 'opacity': '1'});
                    }
                }
            };
            $("#v-pills-tab .nav-link").on("click", function(){
                let hash = $(this).attr('href');
                if(hash == '#v-pills-main'){
                    $('.filter-types').show();
                }else{
                    $('.filter-types').hide();
                }
                window.location.hash = hash;
                $('a.page-link').each(function(){
                    let url = $(this).attr('href');
                    if(url.indexOf('#') !== -1){
                        let arr = url.split('#');
                        url = arr[0] + hash;
                        console.log(arr[0]);
                        console.log(hash);
                    }else{
                        url += hash;
                    }
                    $(this).attr('href', url);
                });
            });
            let hash = window.location.hash;
            $("#v-pills-tab").find(".nav-link[href='" + hash + "']").trigger('click');

            // Mass search
            $("#mass_modal_search_btn").on("click", function(){
                let stock_id = $("#stock").val();
                let equipment_numbers = $("#mass_modal_equipment_numbers").val();
                $.ajax({
                    url: "/get_mass_equipment",
                    data: {
                        stock_id: stock_id,
                        equipment_numbers: equipment_numbers
                    },
                    success(response){
                        $("#mass_modal_result").html(response['html']);
                    },
                    error(response){
                        alert('Возникла ошибка при поиске!');
                        console.log(response);
                    }
                });
            });
            $("#massModal_btn").on("click", function(){
                if($("#stock").val()){
                    $("#massModal").modal('show');
                }else{
                    alert("Выберите склад");
                }
            });

            $("#stock").on("change", function(){
                let btn = $("#movement_story_btn");
                let href = btn.attr("href");
                let stockId = $(this).val();
                let arr = href.split("?");
                btn.attr("href", arr[0] + "?stock_id=" + stockId);
            });
        });
    </script>
@endsection