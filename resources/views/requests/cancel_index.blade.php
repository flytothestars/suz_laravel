@extends('layouts.app')

@section('pageTitle', 'Отмена заявки №' . $request->id)

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
                <h1 class="mt-3 mb-0">Отмена заявки №{{ $request->id }}</h1>
                <h5 class="mb-0 text-muted">Наряд №{{ $request->id_flow }}</h5>
                <h1 class="display-5">Клиент: {{ $request->v_client_title }}</h1>
                <h2 class="mb-0"><i class="fas fa-phone"></i> {{ $request->v_client_cell_phone }}</h2>
            </div>
            <form action="{{ route('request.cancel') }}" method="POST">
                @csrf
                <input type="hidden" name="request_id" value="{{ $request->id }}">
                <div class="card-body">
                    <div class="col-md-6"><hr class="mt-0">
                        <div class="form-group required">
                            <label for="id_reason" class="control-label">Причина:</label>
                            <select name="id_reason" id="id_reason" class="form-control" required>
                                @foreach($reasons as $r)
                                    <option value="{{ $r->id_reason }}">{{ $r->v_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group required">
                            <label for="reason">Примечание</label>
                            <textarea name="reason" id="reason" rows="5" class="form-control"></textarea>
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
                <div class="card-footer py-5">
                    <button id="cancel-btn" type="submit" class="btn btn-primary">Отправить</button>
                    <a href="/requests/{{ $request->id }}" class="btn btn-secondary">Отмена</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('page-scripts')
    <script type="text/javascript">
        $(document).ready(function(){
            function updateCancelButton() {
                var commentVal = $.trim($('#reason').val());

                if (commentVal === '') {
                    $("#cancel-btn").prop("disabled", true);
                } else {
                    $("#cancel-btn").removeAttr("disabled");
                }
            }

            updateCancelButton();

            $('#reason').keyup(updateCancelButton);
        });
    </script>
@endsection
