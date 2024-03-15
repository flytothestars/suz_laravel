@extends('layouts.app')

@section('pageTitle', 'Назначение заявки №' . $request->id)

@section('top-content')
    <a href="/requests/{{ $request->id }}" class="btn btn-secondary top-back-btn"><i class="fas fa-arrow-left"></i> Назад</a>
@endsection

@section('content')
<div class="container-fluid mt-md--7 main">
    <div class="card min-height-500 mb-5 shadow">
        <div class="card-header bg-transparent border-0 mb-0">
            <h1 class="mt-3 mb-0">Назначение заявки №{{ $request->id }}</h1>
            <h5 class="mb-0 text-muted">Наряд №{{ $request->id_flow }}</h5>
            <h1 class="display-4">Клиент: {{ $request->v_client_title }}</h1>
            <h2 class="mb-0"><i class="fas fa-phone"></i> {{ $request->v_client_cell_phone }}</h2>
        </div>
        <form action="{{ route('request.assign') }}" method="POST">
            @csrf
            <input type="hidden" name="request_id" value="{{ $request->id }}">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6"><hr class="mt-0">
                        <div class="form-group">
                            <label for="date">Дата и время:</label>
                            <div class="row">
                                <div class="col-md-6">
                                    <input type="text" id="date" name="date" class="datepicker form-control"
                                           placeholder="Выберите дату" required
                                           value="{{ ($request->date_time && $request->status == 'Назначено') ? date("Y-m-d", strtotime($request->date_time)) : old('date') }}">
                                </div>
                                <div class="col-md-6">
                                    <select id="time" name="time" class="form-control" required>
                                        <option value="0" {{ $request->time == 0 ? 'selected' : '' }}>До обеда</option>
                                        <option value="1" {{ $request->time == 1 ? 'selected' : '' }}>После обеда</option>
                                        <option value="2" {{ $request->time == 2 ? 'selected' : '' }}>В течение дня</option>
                                    </select>
                                </div>
                            </div>
                        </div><hr>
                        <div class="row installers-div">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="installer_1">Монтажник 1:</label>
                                    <select name="installer_1" id="installer_1" class="form-control selectpicker" data-live-search="true" data-style="btn-secondary" required>
                                        <option selected disabled>Не выбрано</option>
                                        @foreach($installers as $in)
                                            <option value="{{ $in->id }}" {{ ($request->installers && $request->installers[0]->id == $in->id) ? 'selected' : '' }}>{{ $in->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="installer_2">Монтажник 2:</label>
                                    <select name="installer_2" id="installer_2" class="form-control selectpicker" data-live-search="true" data-style="btn-secondary">
                                        <option selected disabled>Не выбрано</option>
                                        @foreach($installers as $in)
                                            <option value="{{ $in->id }}" {{ ($request->installers && $request->installers[1]->id == $in->id) ? 'selected' : '' }}>{{ $in->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="comment">Примечание:</label>
                            <textarea name="comment" id="comment" class="form-control"></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                    @if($errors->any())
                        <div class="alert alert-danger">
                            <h5 class="text-white">У вас есть ошибки в форме:</h5>
                            <ul class="m-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                    </div>
                </div>
            </div>
            <div class="card-footer py-5">
                <button type="submit" class="btn btn-success">Назначить</button>
                <a href="/requests/{{ $request->id }}" class="btn btn-secondary">Отмена</a>
            </div>
        </form>
    </div>
</div>
@endsection

@section('page-scripts')
<script type="text/javascript">
    $(document).ready(function(){
        $(".datepicker").datepicker('setStartDate', 'now');
        $('#installer_1').on('changed.bs.select', function(e, clickedIndex, isSelected, previousValue){
            let value = $(this).val();
            $("#installer_2 option").removeAttr("disabled");
            $("#installer_2 option[value=" + value + "]").attr('disabled', 'disabled');
        });
        $('#installer_2').on('changed.bs.select', function(e, clickedIndex, isSelected, previousValue){
            let value = $(this).val();
            $("#installer_1 option").removeAttr("disabled");
            $("#installer_1 option[value=" + value + "]").attr('disabled', 'disabled');
        });
    });
</script>
@endsection

<?php /* <div class="form-group">
        <label>Статус:</label>
        @foreach($statuses as $st)
        <div class="custom-control custom-radio mb-3">
            <input name="status_id" class="custom-control-input" data-name="{{ $st->name }}" id="customRadio{{ $st->id }}" value="{{ $st->id }}" type="radio" {{ $st->id == $request->status_id ? 'checked' : '' }}>
            <label class="custom-control-label" for="customRadio{{ $st->id }}">{{ $st->name }}</label>
        </div>
        @endforeach
    </div> -->
*/?>