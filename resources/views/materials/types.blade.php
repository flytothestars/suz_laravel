@extends('layouts.app')

@section('top-content')
    <a href="/materials" class="btn btn-white top-back-btn"><i class="fas fa-arrow-left"></i> Назад</a>
@endsection

@section('content')
    <div class="container-fluid mt--7">
        <div class="card min-height-500">
            <div class="card-header">
                <div class="row">
                    <div class="col-md-8">
                        <h1>Типы ТМЦ</h1>
                    </div>
                    <div class="col-md-4">
                        <button type="button" class="float-right btn btn-primary" data-toggle="modal" data-target="#typeModal">Добавить тип</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <th>ID</th>
                        <th>Название</th>
                        <th></th>
                    </thead>
                    <tbody>
                    @if($types && $types->count() > 0)
                        @foreach($types as $type)
                            <tr>
                                <td>{{ $type->id }}</td>
                                <td>{{ $type->name }}</td>
                                <td><button class="btn btn-danger delete-type" data-id="{{ $type->id }}">Удалить</button></td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="2" class="text-center">Нет типов</td>
                        </tr>
                    </tbody>
                    @endif
                </table>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="typeModal" tabindex="-1" role="dialog" aria-labelledby="typeModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="typeModalLabel">Новый тип</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="/materials/types/store" method="post">
                    @csrf
                    <div class="modal-body">
                        <label>Название</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Добавить</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('page-scripts')
    <script type="text/javascript">
        $(document).ready(function(){
            $('.delete-type').on('click', function(){
                let id = $(this).data('id');
                if(confirm('Вы уверены, что хотите удалить тип?')){
                    $.ajax({
                        url: '/materials/types/delete',
                        data: {
                            id: id
                        },
                        success(){
                            alert('Тип удален');
                            window.location.reload();
                        }
                    });
                }
            });
        });
    </script>
@endsection