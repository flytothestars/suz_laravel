@extends('layouts.app')

@php
    $pageTitle = 'История перемещений';
@endphp

@section('pageTitle', $pageTitle)

@section('content')
<div class="container-fluid mt--7">
    <div class="card min-height-500 mb-5">
        <div class="card-body">
            <h1>История перемещений оборудования</h1>
            <div class="col-3">
                <div class="nav flex-column nav-pills" id="v-pills-tab" role="tablist" aria-orientation="vertical">
                    <a class="mb-3 nav-link active" id="v-pills-main-tab" data-toggle="pill" href="#v-pills-main" role="tab" aria-controls="v-pills-main" aria-selected="true"><i class="fas fa-folder"></i> История ОС</a>
                    <a class="mb-3 nav-link" id="v-pills-materials-tab" data-toggle="pill" href="#v-pills-materials" role="tab" aria-controls="v-pills-materials" aria-selected="false"><i class="fas fa-folder"></i> История ТМЦ</a>
                </div>
            </div>
            <div class="tab-content" id="v-pills-tabContent">
                <div class="tab-pane fade show active" id="v-pills-main" role="tabpanel" aria-labelledby="v-pills-main-tab">
                     <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <th>#</th>
                                <th>Комплект</th>
                                <th>Текущий владелец</th>
                                <th>Автор</th>
                                <th>Склад</th>
                                <th>Дата</th>
                                <th>Серийный номер</th>
                                <th>Откуда перемещено</th>
                                @if($showRollbackBtn)
                                <th>Откат</th>
                                @endif
                            </thead>
                            <tbody>
                                @foreach($equipment_story as $eq)
                                    <tr>
                                        <td>{{ $eq->id }}</td>
                                        @if(isset($eq->kitname))
                                        <td>{{ $eq->kitname }}</td>
                                        @else
                                        <td>{{ $eq->equipment_id }}</td>
                                        @endif
                                        @if(isset($eq->ownername))
                                        <td>{{ $eq->ownername }}</td>
                                        @else
                                        <td>{{ $eq->owner_id }}</td>
                                        @endif
                                        <td>{{ $eq->username }}</td>
                                        @if(isset($eq->stockname))
                                        <td>{{ $eq->stockname }}</td>
                                        @else
                                        <td style="color: red;">Выдано / перемещено</td>
                                        @endif
                                        <td>{{ $eq->created_at }}</td>
                                        <td>{{ $eq->serial }}</td>
                                        @if(isset($eq->fromname))
                                        <td>{{ $eq->fromname }}</td>
                                        @elseif(isset($eq->from_stockname))
                                        <td>{{ $eq->from_stockname }}</td>
                                        @else
                                        <td>Неизвестно</td>
                                        @endif
                                        @if($showRollbackBtn)
                                            @if (($eq->from || $eq->from_stock) && (in_array($eq->id, $unique)))
                                            <td><a class="btn btn-danger kit-rollback-btn text-white" data-id="{{ $eq->id }}">
                                                <i class="fa fa-trash"></i>&nbsp;Откатить
                                            </a></td>
                                            @else
                                            <td>Недоступно</td>
                                            @endif
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="py-4">
                            {{ $equipment_story->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="v-pills-materials" role="tabpanel" aria-labelledby="v-pills-materials-tab">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <th>#</th>
                                <th>Расходник</th>
                                <th>Текущий владелец</th>
                                <th>Автор</th>
                                <th>Склад</th>
                                <th>Количество</th>
                                <th>Дата</th>
                                <th>Откуда перемещено</th>
                                <th>Возвращен на склад</th>
                            </thead>
                            <tbody>
                                @foreach($materials_story as $ms)
                                    <tr>
                                        <td>{{ $ms->id }}</td>
                                        @if(isset($ms->materialname))
                                        <td>{{ $ms->materialname }}</td>
                                        @else
                                        <td>{{ $ms->material_id }}</td>
                                        @endif
                                        @if(isset($ms->ownername))
                                        <td>{{ $ms->ownername }}</td>
                                        @else
                                        <td>{{ $ms->owner_id }}</td>
                                        @endif
                                        @if(isset($ms->username))
                                        <td>{{ $ms->username }}</td>
                                        @else
                                        <td></td>
                                        @endif
                                        <td>{{ $ms->stockname }}</td>
                                        <td>{{ $ms->qty }}</td>
                                        <td>{{ $ms->created_at }}</td>
                                        @if(isset($ms->fromname))
                                        <td>{{ $ms->fromname }}</td>
                                        @elseif(isset($ms->from_stockname))
                                        <td>{{ $ms->from_stockname }}</td>
                                        @else
                                        <td>Неизвестно</td>
                                        @endif
                                        @if ($ms->returned == 1)
                                        <td><i class="fas fa-check text-success"></i></td>
                                        @else
                                        <td>Нет</td>
                                        @endif
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="py-4">
                            {{ $materials_story->appends(request()->query())->links() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection

@section('page-scripts')
<script type="text/javascript">
    $(document).ready(function(){
        $(".kit-rollback-btn").on("click", function(){
            let id = $(this).data('id');
            if(confirm('Вы действительно хотите откатить данную операцию?')){
                $.ajax({
                    url: '/kit_rollback',
                    type: 'POST',
                    dataType: 'json',
                    data: {
                        "_token": $('meta[name="csrf-token"]').attr('content'),
                        id: id
                    },
                    success(response){
                        alert(response['message']);
                        if(response['success']){
                            window.location.reload();
                        }
                    }
                });
            }
        });
        $("#v-pills-tab .nav-link").on("click", function(){
                let hash = $(this).attr('href');
                window.location.hash = hash;
                $('a.page-link').each(function(){
                    let url = $(this).attr('href');
                    if(url.indexOf('#') !== -1){
                        let arr = url.split('#');
                        url = arr[0] + hash;
                        console.log(arr[0]);
                        console.log(hash);
                    }else{
                        url += hash;
                    }
                    $(this).attr('href', url);
                });
            });
        let hash = window.location.hash;
        $("#v-pills-tab").find(".nav-link[href='" + hash + "']").trigger('click');
    });
</script>
@endsection