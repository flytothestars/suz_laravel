@extends('layouts.app')

@section('pageTitle', 'Отложение заявки №' . $request->id)

@section('top-content')
    <a href="/requests/{{ $request->id }}" class="btn btn-secondary top-back-btn"><i class="fas fa-arrow-left"></i> Назад</a>
@endsection

@section('content')
    <div class="container-fluid mt-md--7 main">
        <div class="card min-height-500 mb-5 shadow">
            <div class="card-header bg-transparent border-0 mb-0">
            @if(session('message'))
            <div class="row">
                <div class="col-md-12">
                    <div class="alert alert-{{ session('message') == 'Расходники успешно записаны в базу.' ? 'success' : 'danger' }}">{{ session('message') }}</div>
                </div>
            </div>
            @endif
                <h1 class="mt-3 mb-0">Отложение заявки №{{ $request->id }}</h1>
                <h5 class="mb-0 text-muted">Наряд №{{ $request->id_flow }}</h5>
                <h1 class="display-5">Клиент: {{ $request->v_client_title }}</h1>
                <h2 class="mb-0"><i class="fas fa-phone"></i> {{ $request->v_client_cell_phone }}</h2>
            </div>
            <form action="{{ route('request.postpone') }}" method="POST">
                @csrf
                <input type="hidden" name="request_id" value="{{ $request->id }}">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6"><hr class="mt-0">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group required">
                                        <label for="date" class="control-label">Дата:</label>
                                        <input type="text" id="date" name="date" class="form-control datepicker" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group required">
                                        <label for="time" class="control-label">Время:</label>
                                        <select name="time" id="time" class="form-control">
                                            @foreach($time_intervals as $ti)
                                                <option>{{ date("H:i", $ti) }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group required">
                                <label for="reason" class="control-label">Причина:</label>
                                <textarea name="reason" id="reason" rows="5" class="form-control" required></textarea>
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
                    <button id="postpone-btn" type="submit" class="btn btn-primary">Отправить</button>
                    <a href="/requests/{{ $request->id }}" class="btn btn-secondary">Отмена</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page-scripts')
<script type='text/javascript'>
    $(document).ready(function(){
        $("#date").datepicker('setStartDate', new Date());

        function updatePostponeButton() {
            var commentVal = $.trim($('#reason').val());

            if (commentVal === '') {
                $("#postpone-btn").prop("disabled", true);
            } else {
                $("#postpone-btn").removeAttr("disabled");
            }
        }

        updatePostponeButton();

        $('#reason').keyup(updatePostponeButton);
    });
</script>
@endsection
