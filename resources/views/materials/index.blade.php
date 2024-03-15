@extends('layouts.app')

@php
    $pageTitle = 'Расходные материалы';
    $selectedType = isset($_GET['type']) ? $_GET['type'] : '';
@endphp

@section('top-content')
    <a href="/settings" class="btn btn-white top-back-btn"><i class="fas fa-arrow-left"></i> Вернуться в настройки</a>
@endsection

@section('pageTitle', $pageTitle)

@section('content')
    <div class="container-fluid mt--7">
        @csrf
        <div class="card min-height-500">
            <div class="card-header border-0">
                @if(session('message'))
                    <div class="my-4 alert alert-{{ session('success') ? 'success' : 'danger' }} alert-temporary">{{ session('message') }}</div>
                @endif
                <div class="row">
                    <div class="col-md-6">
                        <h1>{{ $pageTitle }}</h1>
                    </div>
                    <div class="col-md-6">
                        <a href="/materials/types" class="float-right btn btn-light">Типы ТМЦ</a>
                        <a href="/materials/create" class="float-right btn btn-light mr-3">Добавить расходники</a>
                    </div>
                </div>
                <div class="row my-3">
                    <div class="col-md-12">
                        <form action="" class="form-inline">
                            <div class="form-group">
                                <label>Тип:</label>
                                <select name="type" id="type" class="form-control ml-2">
                                    <option value="all">Все</option>
                                    @foreach($types as $type)
                                        <option {{ $selectedType == $type->id ? 'selected' : '' }} value="{{ $type->id }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group ml-3">
                                <button class="d-block btn btn-primary">Применить</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body pb-5">
                @if($materials)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <th>#</th>
                            <th>Название</th>
                            <th>Тип</th>
                            <th>Лимиты</th>
                            <th></th>
                        </thead>
                        <tbody>
                            @foreach($materials as $key => $m)
                                <tr>
                                    <td>{{ (json_decode($materials->toJson())->current_page-1)*15+($key+1) }}</td>
                                    <td>{{ $m->name }}</td>
                                    <td>{{ $m->type }}</td>
                                    <td><input type="number" id="limit_qty" data-id="{{ $m->id }}" value="{{ $m->limit_qty }}"></td>
                                    <td><button class="btn btn-danger delete-material" data-id="{{ $m->id }}">Удалить</button></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-3">
                        {{ $materials->appends(request()->query())->links() }}
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('page-scripts')
    <script type="text/javascript" src="{{ asset('js/jquery.cookie.js') }}"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function(){
            el = document.getElementById('limit_qty');
            el.addEventListener('change', editLimit);
            function editLimit(){
                if(confirm('Изменить лимит?')){
                    let id = $(this).data('id');
                    let limit_qty = $('#limit_qty').val();
                    $.ajax({
                        url: '/materials/edit_limit',
                        type: 'post',
                        data: {
                            _token: $("input[name='_token']").val(),
                            id: id,
                            limit_qty: limit_qty
                        },
                        success(response){
                            alert(response['message']);
                            window.location.reload();
                        }
                    });
                }
            };
            $('#type').on('change', function(){
                $.cookie('material_type', $(this).val(), { expires: 1, path: '/' });
            });
            $('.delete-material').on('click', function(){
                if(confirm('Вы уверены?')){
                    let id = $(this).data('id');
                    $.ajax({
                        url: '/materials/delete',
                        type: 'post',
                        data: {
                            _token: $("input[name='_token']").val(),
                            id: id
                        },
                        success(response){
                            alert(response['message']);
                            window.location.reload();
                        }
                    });
                }
            });
        });
    </script>
@endsection