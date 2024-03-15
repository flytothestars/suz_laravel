@extends('layouts.app')

@section('pageTitle', 'Завершение заявки №' . $request_id)

@section('top-content')
    <a href="/requests/{{ $request_id }}" class="btn btn-secondary top-back-btn"><i class="fas fa-arrow-left"></i> Назад</a>
@endsection
    <link rel="stylesheet" href="/css/main.css">
@section('content')
<div class="container-fluid mt-md--7 main">
    <div class="card min-height-500 mb-5 shadow">
        <div class="card-header bg-transparent border-0 mb-0">
            <h1 class="mt-3 mb-0">Завершение заявки №{{ $request_id }}</h1>
            <h5 class="mb-0 text-muted">Наряд №{{ $request_id_flow }}</h5>
            <h1 class="display-4">Клиент: {{ $client_title }}</h1>
            @if($count < 3 && isset($latitude) && isset($longitude))
            <div class="col-md-6">
                <div class="card shadow">
                    <div id="map-complete" style="width: 100%; height: 500px"></div>
                </div>
                <button type="submit" class="btn btn-success coordinates" onclick="getCenter();enableButton();showAlert()">Записать координаты</button>
                <input type="hidden" style="width: 500px;" id="latitude" name="latitude" value="{{ $latitude ?? '' }}">
                <input type="hidden" style="width: 500px;" id="longitude" name="longitude" value="{{ $longitude ?? '' }}">
            </div>
            <div class="col-md-6">
                <div class="alert alert-secondary" id="success-alert">
                    <button type="button" class="close" data-dismiss="alert">x</button>
                    <strong>Координаты записаны</strong>
                </div>
            </div>
            @endif
        </div>

        <form id="upload_image" action="{{ route('ajaxImageUpload') }}" enctype="multipart/form-data" method="POST">
            @csrf
            <input type="hidden" name="request_id" value="{{ $request_id }}">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6"><hr class="mt-0">
        			<div id="form-messages" class="alert" role="alert" style="display: none;"></div>
        			<div class="mb-2" id="loading"></div>
                        <div class="form-group">
                            <label>Прикрепить фото к заявке (не более 3, типы - jpeg, jpg, png, gif):</label>
                            <input type="file" name="file[]" class="form-control" multiple>
                        </div>
                        <div class="form-group">
                            <button class="btn btn-success upload-image">Загрузить</button>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <form action="/requests/{{ $request_id }}/completeAction" method="POST">
            @csrf
            <input type="hidden" name="installers" value="{{ $installers }}">
            <input type="hidden" name="installer_take" value="{{ $installer_take }}">
            <input type="hidden" name="installer_give" value="{{ $installer_give }}">
            <input type="hidden" name="request_id" value="{{ $request_id }}">
            <input type="hidden" name="equipments_to_take" value="{{ $equipments_to_take ?? '' }}">
            <input type="hidden" name="kits_to_give" value="{{ $kits_to_give ?? '' }}">
            <input type="hidden" name="kits_to_take" value="{{ $kits_to_take ?? '' }}">
            @if($v_kits_transfer)
            <input type="hidden" name="v_kits_transfer" value="{{ $v_kits_transfer }}">
            @endif
            @if($b_unbind_cntr)
            <input type="hidden" name="b_unbind_cntr" value="{{ $b_unbind_cntr }}">
            @endif
            <input type="hidden" name="v_param_internet" value="{{ $v_param_internet ?? null }}">

            <input type="hidden" name="materials_take" value="{{ $materials_take ?? null }}">
            <input type="hidden" name="materials_qty_take" value="{{ $materials_qty_take ?? null }}">
            <input type="hidden" name="materials_give" value="{{ $materials_give ?? null }}">
            <input type="hidden" name="materials_qty_give" value="{{ $materials_qty_give ?? null }}">

            <input type="hidden" name="dt_birthday" value="{{ $dt_birthday }}">
            <input type="hidden" name="v_iin" value="{{ $v_iin }}">
            <input type="hidden" name="v_document_number" value="{{ $v_document_number }}">
            <input type="hidden" name="dt_document_issue_date" value="{{ $dt_document_issue_date }}">
            <input type="hidden" name="v_document_series" value="{{ $v_document_series }}">
            <input type="hidden" name="use_clients_equipment" value="{{ $use_clients_equipment ?? '' }}">

            <input type="hidden" id="coordinates" name="coordinates" value="">
            @if($repair_type)
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6"><hr class="mt-0">
                        <div class="form-group">
                            <select id="id_type" name="id_type" class="form-control select-repair">
                                <option value="0" selected>Выберите тип ремонта (обязательно):</option>
                                @foreach($repair_type as $type)
                                    <option value="{{ $type->id_type }}">{{ $type->v_name }}</option>
                                @endforeach
                            </select>
			        	</div>
			        </div>
			    </div>
			</div>
	        @endif
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6"><hr class="mt-0">
                        <div class="form-group required">
                            <label for="comment">Комментарий:</label>
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
                @if($count < 3 && isset($latitude) && isset($longitude))
                    <button onclick="openModal();" type="button" id="complete-btn" class="btn btn-success" disabled>Завершить</button>
                @else
                    <button onclick="openModal();" type="button" id="complete-btn" class="btn btn-success">Завершить</button>
                @endif
                <a href="/requests/{{ $request_id }}" class="btn btn-secondary">Отмена</a>
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
                <button type="button" id="deflectButtonMaterial" class="btn btn-secondary" data-dismiss="modal" onclick="hideButton($('#write_to_db')[0],this)">Отменить</button>
                <form action="/requests/{{ $request_id }}/material" style="margin-block-end:0;" method="post"
                      class="d-inline-block mr-2 float-right">
                    @csrf
                    <input type="hidden" name="installer_take" value="{{$installer_take}}">
                    <input type="hidden" name="installer_give" value="{{$installer_give}}">
                    <input type="hidden" name="materials_take" value="{{$materials_take}}">
                    <input type="hidden" name="materials_qty_take" value="{{$materials_qty_take}}">
                    <input type="hidden" name="materials_give" value="{{$materials_give}}">
                    <input type="hidden" name="materials_qty_give" value="{{$materials_qty_give}}">
                    <input type="hidden" name="status_type" value="{{$status_type}}">
                    <input type="hidden" name="place" value="complete">
                    <input type="hidden" name="request_id" value="{{$request_id}}">
                    <input type="hidden" name="data" value="{{json_encode($request_data)}}">
                    <input type="hidden" name="comment" id="commentSample">
                    <input type="hidden" name="id_type" id="id_type_modal" value="">
                    <input type="hidden" name="limit_input" id="limit_input" value="{{$limit_input}}">

                    <button type="submit" class="btn btn-success" id="write_to_db" onclick="hideButton(this,$('#deflectButtonMaterial')[0])">Подтвердить</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-scripts')
    <script src="https://api-maps.yandex.ru/2.1/?apikey=74e8af9b-ed77-46cb-bc38-71da808481bd&lang=ru_RU" type="text/javascript"></script>
    <script type="text/javascript" src="{{ asset('js/geolocated_map.js') }}"></script>
    <script>
        function openModal() {
            if ($('input[name="materials_take"]').val() !== "[]" || $('input[name="materials_give"]').val() !== "[]") {
                $('#commentSample').val($('#comment').val());
                $('#materialConfirm').modal('show');
            } else {
                $('#complete-btn').closest('form').submit();
            }
        }

        function hideButton(accept,deflect) {
            let idTypeValue = $('#id_type').val();
            $('#id_type_modal').val(idTypeValue);

            accept.style.display = 'none';
            deflect.style.display = 'none';
        }

        $(document).ready(function(){
            function updateCompleteButton() {
                var selectRepairVal = $('.select-repair').val();
                var commentVal = $.trim($('#comment').val());

                if (selectRepairVal == 0 || commentVal === '') {
                    $("#complete-btn").prop("disabled", true);
                } else {
                    $("#complete-btn").removeAttr("disabled");
                }
            }

            updateCompleteButton();

            $('.select-repair').change(updateCompleteButton);
            $('#comment').keyup(updateCompleteButton);

            $(function() {
                var form = $('#upload_image');
                var formMessages = $('#form-messages');
                form.submit(function(event) {
                    event.preventDefault();
                    var $fileUpload = $("input[type='file']");
                    if (parseInt($fileUpload.get(0).files.length) > 3){
                        formMessages.removeClass('alert-success');
                        formMessages.removeClass('alert-danger');
                        formMessages.addClass('alert-warning');
                        formMessages.text('Вы можете загрузить не больше трёх фотографий.');
                        formMessages.fadeTo(5000, 500).slideUp(500, function() {
                            formMessages.slideUp(500);
                        });
                    }
                    else{
                        var formData = new FormData($(this)[0]);
                        $.ajax({
                            type: 'POST',
                            url: form.attr('action'),
                            data: formData,
                            contentType: false,
                            processData: false,
                            beforeSend: function(){
                                $("#loading").show();
                                $("#loading").html("Подождите, фотографии загружаются...");
                            },
                            success:function(response){
                                if (response.success == true){


                                    formMessages.removeClass('alert-danger');
                                    formMessages.removeClass('alert-warning');
                                    formMessages.addClass('alert-success');
                                    formMessages.text(response.message);
                                    formMessages.fadeTo(10000, 500).slideUp(500, function() {
                                        formMessages.slideUp(500);
                                    });
                                    $(".upload-image").prop('disabled', true);
                                    $("#loading").hide();
                                }
                                else{

                                    formMessages.removeClass('alert-success');
                                    formMessages.removeClass('alert-warning');
                                    formMessages.addClass('alert-danger');
                                    formMessages.text(response.message);
                                    formMessages.fadeTo(7000, 500).slideUp(500, function() {
                                        formMessages.slideUp(500);
                                    });
                                    $("#loading").hide();
                                }
                            }
                        });
                    }

                });
            });
        });
    </script>
@endsection

