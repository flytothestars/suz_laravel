@extends('layouts.app')

@section('pageTitle', 'Редактирование заявки №' . $request->id)

@section('top-content')
    <a href="{{ route('requests') }}" class="btn btn-secondary top-back-btn"><i class="fas fa-arrow-left"></i> Назад</a>
@endsection

@section('content')
<div class="container-fluid mt--7">
    <div class="card min-height-500">
        <div class="card-header">
            <h1 class="mt-3 mb-0">Редактирование заявки №{{ $request->id }}</h1>
            <h5 class="text-muted">Наряд №{{ $request->id_flow }}</h5>
            <h1 class="display-3">Клиент: {{ $request->v_client_title }}</h1>
            <h2>{{ $request->v_client_cell_phone }}</h2>
        </div>
        <div class="card-body">
            <div class="col-md-8">
                <form action="">
                    <div class="form-group row">
                        <label for="edit_contract" class="col-md-2 col-form-label">Номер договора</label>
                        <div class="col-md-8">
                            <input type="number" class="form-control" id="edit_contract" readonly value="{{ $request->v_contract }}">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="edit_ci_flow" class="col-md-2 col-form-label">Тип заказа</label>
                        <div class="col-md-8">
                            <input type="text" class="form-control" value="{{ $request->ci_flow }}" readonly>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection