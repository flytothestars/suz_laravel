@extends('layouts.app')

@section('pageTitle', 'Маршрутный лист №' . $routeList->id)

@section('content')

<div class="container-fluid mt-md--7 main">
    <div class="card min-height-500 mb-5 shadow">
        <div class="card-header bg-transparent">
			<h1>Маршрутный лист №{{ $routeList->id }}</h1>
    	</div>
    	<div class="card-body">
            <div class="row">
                <div class="col-md-6">
            		<div class="mb-3">Дата: <span class='font-weight-bold'>{{ $routeList->date }}</span></div>
            		<div class="mb-3">Участок: <span class='font-weight-bold'>{{ $routeList->location }}</span></div>
            		<label>Монтажники:</label>
            		<ul>
            			@foreach($routeList->installers() as $inst)
            				<li><a href="/users/{{ $inst->id }}">{{ $inst->name }}</a></li>
            			@endforeach
            		</ul>
                </div>
                <div class="col-md-6">
                    <h3>Заявки</h3>
                    <ul class="pl-3">
                    @foreach($routeList->requests as $req)
                        <li><a href="/requests/{{ $req->id }}">{{ $req->id_flow }}</a> - {{ $req->getStatus()->name }}</li>
                    @endforeach
                    </ul>
                </div>
            </div>
    	</div>
    </div>
</div>

@endsection