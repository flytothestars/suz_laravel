@extends('layouts.app')

@section('pageTitle', 'Роли')

@section('content')
<div class="container-fluid mt--7">
    <div class="card min-height-500">
        <div class="card-body">
            <h1>Роли</h1>
            <div class="row">
                <div class="col-md-12">
                    <div class="table-responsive">
                        <table class='table table-bordered'>
                            <thead>
                                <th><b>Разрешения</b></th>
                                @foreach($roles as $role)
                                <th><b>{{ $role->name }}</b></th>
                                @endforeach
                            </thead>
                            <tbody>
                                @foreach($permissions as $permission)
                                <tr>
                                    <td>{{ $permission->name }}</td>
                                    @foreach($roles as $role)
                                    <td>{!! ($role->hasPermissionTo($permission->name)) ? '<i class="fas fa-check text-success"></i>' : '<i class="fas fa-times text-danger"></i>' !!}</td>
                                    @endforeach
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
@endsection