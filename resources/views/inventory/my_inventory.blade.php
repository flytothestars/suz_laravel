@extends('layouts.app')

@php
    $pageTitle = 'Мой инвентарь';
    $selectedDepartment = isset($_GET['department']) ? $_GET['department'] : '';
    $selectedLocation = isset($_GET['location']) ? $_GET['location'] : '';
@endphp

@section('pageTitle', $pageTitle)

@section('content')
    <div class="container-fluid mt--7">
        <div class="card min-height-500">
            <div class="card-header">
                @if(session('message'))
                    <div class="my-4 alert alert-{{ session('success') ? 'success' : 'danger' }} alert-temporary">{{ session('message') }}</div>
                @endif

                    @if($errors->any()) <!-- Check if there are any validation errors -->
                    <div class="alert alert-danger">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li> <!-- Display each error message -->
                            @endforeach
                        </ul>
                    </div>
                    @endif

                <h1>{{ $pageTitle }}</h1>
                @if(\Auth::user()->locations->count() > 0)
                <div class="h5 font-weight-400">
                    <i class="fas fa-map-marker-alt"></i>
                    @foreach(\Auth::user()->locations as $key => $loc)
                        {{ $loc->v_name }}
                    @endforeach
                </div>
                @else
                <h5 class="font-weight-400">Не привязан к участку</h5>
                @endif
            </div>

            <div class="card-body">
            <form method="get" action="">
                <div class="input-group stylish-input-group mb-3">
                    <input type="text" id="searchKit" class="form-control" placeholder="Найти комплект по серийному номеру">
                </div>
            </form>
                <div class="row">
                    <div class="col-md-12">
                        <ul class="nav nav-tabs" id="myTab" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="kits-tab" data-toggle="tab" href="#kits" role="tab" aria-controls="kits" aria-selected="true">Комплекты</a>
                            </li>
                            <!-- <li class="nav-item">
                                <a class="nav-link" id="equipments-tab" data-toggle="tab" href="#equipments" role="tab" aria-controls="equipments" aria-selected="false">Экземпляры</a>
                            </li> -->
                            <li class="nav-item">
                                <a class="nav-link" id="materials-tab" data-toggle="tab" href="#materials" role="tab" aria-controls="materials" aria-selected="false">Расходники</a>
                            </li>
                        </ul>
                        <div class="tab-content mt-4" id="myTabContent">
                            <div class="tab-pane fade show active" id="kits" role="tabpanel" aria-labelledby="kits-tab">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <th>Мнемоника</th>
                                            <th>Серийный номер</th>
                                            <th>Количество оборудования</th>
                                            <th>Время получения</th>
                                        </thead>
                                        <tbody id="my-kits-table">
                                        @if($kits && count($kits) > 0)
                                            @foreach($kits as $kit)
                                                <tr>
                                                    <td>
                                                        <button data-id="{{ $kit->id }}" class="btn btn-white show-kit"><i class="fas fa-list"></i></button>
                                                        <span class="h5 equipment-model">{{ $kit->v_type ?? '-' }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="h5">{{ $kit->v_serial ?? '-' }}</span>
                                                    </td>
                                                    <td>{{ $kit->equipments->count() }}</td>
                                                    <td>{{ $kit->updated_at }}</td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr class="text-center">
                                                <td colspan="4">У вас нет комплектов.</td>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                    @if($kits && count($kits) > 0)
                                    <div class="my-3">
                                        {{ $kits->links() }}
                                    </div>
                                    @endif
                                </div>
                            </div>
                            <div class="tab-pane fade" id="equipments" role="tabpanel" aria-labelledby="equipments-tab">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <th>Модель</th>
                                            <th>Вендор</th>
                                            <th>Количество</th>
                                            <th></th>
                                        </thead>
                                        <tbody>
                                        @if($equipments && count($equipments) > 0)
                                            @foreach($equipments as $id_equipment_model => $inner_equipments)
                                                <tr>
                                                    <td>
                                                        <span class="h5 equipment-model">{{ $inner_equipments[0]->model->v_name ?? 'Модель неизвестна' }}</span>
                                                    </td>
                                                    <td>
                                                        <span class="h5 equipment-vendor text-danger">{{ $inner_equipments[0]->model->v_vendor ?? 'Вендор неизвестен' }}</span>
                                                    </td>
                                                    <td>{{ $inner_equipments->count() }}</td>
                                                    <td>
                                                        <button class="float-right btn btn-danger btn-sm align-initial return-equipment-btn"
                                                                data-id="{{ $inner_equipments[0]['id'] }}">
                                                            <i class="fas fa-upload"></i> Вернуть
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @else
                                            <tr class="text-center">
                                                <td colspan="4">Нет оборудования.</td>
                                            </tr>
                                        @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="tab-pane fade" id="materials" role="tabpanel" aria-labelledby="materials-tab">
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead>
                                            <th>Название</th>
                                            <th>Тип</th>
                                            <th>Количество</th>
                                            <th></th>
                                        </thead>
                                        <tbody>
                                            @foreach($materials as $m)
                                                <tr>
                                                    <td>{{ $m->name }}</td>
                                                    <td><b>{{ $m->type }}</b></td>
                                                    <td>{{ $m->qty }}</td>
                                                    <td><button class="btn btn-danger return-materials-btn" data-id="{{ $m->id }}"><i class="fas fa-upload"></i> Вернуть</button></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="returnModal" tabindex="-1" role="dialog" aria-labelledby="returnModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content"></div>
        </div>
    </div>
@endsection

@section('page-scripts')
    <script type="text/javascript">
        $(document).ready(function(){
            $('body').on('keyup', '#searchKit', function () {
                var searchQuery = $(this).val();
                $.ajax({
                    method: 'POST',
                    url: '{{ route("searchKit") }}',
                    dataType: 'json',
                    data:{
                        '_token': '{{ csrf_token() }}',
                        searchQuery: searchQuery
                    },
                    success: function(response){
                        console.log(response);
                        var tableRow = '';
                        $('#my-kits-table').html(response);
                    }
                });
            });
            $('#my-kits-table').on('click', '.show-kit', function() {
                var kitId = $(this).attr('data-id');
                let modal = $('#returnModal');
                $.ajax({
                    url: '/getKitsReturnModal',
                    data: {
                        kitId: kitId
                    },
                    dataType: 'json',
                    success(response){
                        modal.find('.modal-content').html(response['html']);
                        modal.modal('show');
                    }
                });
            });
            $('.show-kit').on('click', function(){
                let kitId = $(this).data('id');
                let modal = $('#returnModal');
                $.ajax({
                    url: '/getKitsReturnModal',
                    data: {
                        kitId: kitId
                    },
                    dataType: 'json',
                    success(response){
                        modal.find('.modal-content').html(response['html']);
                        modal.modal('show');
                    }
                });
            });
            $('.return-equipment-btn').on('click', function(){
                let equipmentId = $(this).data('id');
                let modal = $('#returnModal');
                $.ajax({
                    url: '/getEquipmentsReturnModal',
                    data: {
                        equipment_id: equipmentId
                    },
                    dataType: 'json',
                    success(response){
                        modal.find('.modal-content').html(response['html']);
                        modal.modal('show');
                    }
                });
            });
            $('#return_kit').on('submit', function(e){
                let checked = $("input[name='equipments[]']:checked").length;
                if(!checked){
                    alert("Выберите хотя бы один экземпляр.");
                    e.preventDefault();
                    return false;
                }
            });
            $('#return_equipment').on('submit', function(e){
                let checked = $("input[name='equipments[]']:checked").length;
                if(!checked){
                    alert("Выберите хотя бы один экземпляр.");
                    e.preventDefault();
                    return false;
                }
            });
            $("#myTab .nav-link").on("click", function(){
                let hash = $(this).attr('href');
                window.location.hash = hash;
            });
            let hash = window.location.hash;
            $("#myTab").find(".nav-link[href='" + hash + "']").trigger('click');

            $("body").on("click", ".select-all-equipments-to-return", function(){
                let checkBoxes = $("#return_equipment").find("input[name='return_equipments[]']");
                checkBoxes.not(this).prop('checked', this.checked);
            });
            $('.return-materials-btn').on('click', function(){
                let id = $(this).data('id');
                let modal = $('#returnModal');
                $.ajax({
                    url: '/getMaterialsReturnModal',
                    data: {
                        id: id
                    },
                    dataType: 'json',
                    success(response){
                        modal.find('.modal-content').html(response['html']);
                        modal.modal('show');
                    }
                });
            });
        });
    </script>
@endsection
