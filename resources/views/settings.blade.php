@extends('layouts.app')

@section('content')
    <div class="container-fluid mt-4">
        <h1>Настройки</h1>
        <div class="row text-center">
            <div class="col-md-3">
                <div class="card rounded-0 shadow-sm--hover cursor-pointer">
                    <a href="/stocks">
                        <div class="card-body">
                            <i class="fas fa-cubes fa-2x text-orange"></i><br>
                            <span class="text-dark">Склады</span>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card rounded-0 shadow-sm--hover cursor-pointer">
                    <a href="/materials">
                        <div class="card-body">
                            <i class="fas fa-tools fa-2x text-primary"></i><br>
                            <span class="text-dark">Расходники</span>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card rounded-0 shadow-sm--hover cursor-pointer">
                    <a href="/logs">
                        <div class="card-body">
                            <i class="far fa-file-alt fa-2x text-success"></i><br>
                            <span class="text-dark">Логи</span>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card rounded-0 shadow-sm--hover cursor-pointer">
                    <a href="/equipment">
                        <div class="card-body">
                            <i class="fas fa-hdd fa-2x text-success"></i><br>
                            <span class="text-dark">Оборудование</span>
                        </div>
                    </a>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card rounded-0 shadow-sm--hover cursor-pointer">
                    <a href="/check-olts">
                        <div class="card-body">
                            <i class="fas fa-hdd fa-2x text-success"></i><br>
                            <span class="text-dark">Проверить оборудование</span>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
