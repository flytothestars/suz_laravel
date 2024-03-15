@extends('layouts.app')

@section('pageTitle', 'Возврат заявки №' . $request->id)

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
            <h1 class="mt-3 mb-0">Возврат заявки №{{ $request->id }}</h1>
            <h5 class="mb-0 text-muted">Наряд №{{ $request->id_flow }}</h5>
            <h1 class="display-4">Клиент: {{ $request->v_client_title }}</h1>
            <div class="h4 text-danger">Номер договора: {{ $request->v_contract }}</div>
            <h2 class="mb-0"><i class="fas fa-phone"></i> {{ $request->v_client_cell_phone }}</h2>
        </div>
        <form action="/requests/{{ $request->id }}/returnAction" method="POST">
            @csrf
            <input type="hidden" name="request_id" value="{{ $request->id }}">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group required">
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
                <button onclick="openModal();" type="button" id="return-btn" class="btn btn-success">Вернуть</button>
                <a href="/requests/{{ $request->id }}" class="btn btn-secondary">Отмена</a>
            </div>
        </form>
    </div>
</div>

<!-- Modal -->
<div class="modal fade" id="materialConfirm" tabindex="-1" role="dialog" aria-labelledby="materialConfirmTitle"
     aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="materialConfirmTitle">Добавить/забрать расходники</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                Подтвердите запись указанных расходников
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Отменить</button>
                <form action="/requests/{{ $request->id }}/material" method="post"
                      class="d-inline-block mr-2 float-right">
                    @csrf
                    <input type="hidden" name="installer_take" value="{{$installer_take}}">
                    <input type="hidden" name="installer_give" value="{{$installer_give}}">
                    <input type="hidden" name="materials_take" value="{{$materials_take}}">
                    <input type="hidden" name="materials_qty_take" value="{{$materials_qty_take}}">
                    <input type="hidden" name="materials_give" value="{{$materials_give}}">
                    <input type="hidden" name="materials_qty_give" value="{{$materials_qty_give}}">
                    <input type="hidden" name="status_type" value="undo">
                    <input type="hidden" name="place" value="return">
                    <input type="hidden" name="data" value="{{$request}}">
                    <input type="hidden" name="comment" id="commentSample">
                    <input type="hidden" name="limit_input" id="limit_input" value="{{$limit_input}}">

                    <button type="submit" class="btn btn-success" id="write_to_db">Подтвердить</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-scripts')
<script type="text/javascript">
    function openModal() {

        if ($('input[name="materials_take"]').val() !== "[]" || $('input[name="materials_give"]').val() !== "[]") {
            $('#commentSample').val($('#comment').val());
            $('#materialConfirm').modal('show');
        } else {
            $('#return-btn').closest('form').submit();
        }
    }

    $(document).ready(function(){
        function updateReturnButton() {
            var commentVal = $.trim($('#comment').val());

            if (commentVal === '') {
                $("#return-btn").prop("disabled", true);
            } else {
                $("#return-btn").removeAttr("disabled");
            }
        }

        updateReturnButton();

        $('#comment').keyup(updateReturnButton);
    });
</script>
@endsection
