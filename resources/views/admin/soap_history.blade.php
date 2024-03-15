@extends('layouts.app')

@section('content')
<div class="container-fluid mt-md--7 main">
    <div class="card min-height-500 mb-5 shadow">
        <div class="card-header bg-transparent border-0">
			<h1>Поиск SOAP-данных заявки</h1>
        </div>
        <div class="card-body">
        	<div class="row">
        		<div class="col-md-4">
        			<form action="">
        				<div class="form-group">
        					<label for="q">Введите номер заявки или номер наряда</label>
        					<input type="text" name="q" autofocus class="form-control" value="{{ isset($_GET['q']) ? $_GET['q'] : '' }}">
        				</div>
        				<button class="btn btn-primary">Найти</button>
        			</form>
        		</div>
        	</div>
        	<div class="row mt-4">
        		<div class="col-md-12">
        			<h2>Параметры запроса CloseFlow:</h2>
        			<textarea class="form-control" readonly>{{ $closeflow ?? '' }}</textarea>
        		</div>
        	</div>
        </div>
    </div>
</div>
@endsection