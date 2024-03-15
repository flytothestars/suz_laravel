@extends('layouts.app')

@section('pageTitle', 'Отчеты')

@php
	$date_from = isset($_GET['date_from']) ? $_GET['date_from'] : '';
	$date_to = isset($_GET['date_to']) ? $_GET['date_to'] : '';
	$department = isset($_GET['department']) ? $_GET['department'] : '';
	$stock = isset($_GET['stock']) ? $_GET['stock'] : '';
@endphp

@section('content')
    <div class="container-fluid mt-md--7 main">
        <div class="card min-height-500 mb-5 shadow">
            <div class="card-header bg-transparent border-0 mb-0">
                <h1 class="mt-3 mb-0">Отчеты</h1>
            </div>
            <div class="card-body">
            	<ul class="nav nav-tabs" id="myTab" role="tablist">
	                <li class="nav-item">
	                    <a class="nav-link active" id="report_tab_link" data-toggle="tab" href="#report_tab" role="tab" aria-controls="report_tab" aria-selected="true">Детализация</a>
	                </li>
	                @if(auth()->user()->hasRole('администратор'))
	                <li class="nav-item">
	                    <a class="nav-link" id="consolidated_tab_link" data-toggle="tab" href="#consolidated_tab" role="tab" aria-controls="consolidated_tab" aria-selected="false">Свод</a>
	                </li>
	                <li class="nav-item">
	                    <a class="nav-link" id="balance_tab_link" data-toggle="tab" href="#balance_tab" role="tab" aria-controls="balance_tab" aria-selected="false">Текущий остаток</a>
	                </li>
	                @endif
	                <li class="nav-item">
	                    <a class="nav-link" id="requests_tab_link" href="/requests-report" aria-controls="requests_tab" aria-selected="false">Отчет по заявкам</a>
	                </li>
	            </ul>
				<div class="tab-content" id="myTabContent">
					<div class="tab-pane fade show active" id="report_tab" role="tabpanel" aria-labelledby="report_tab">
		        		<form action="">
		        			<div class="row mt-4">
			            		<div class="col-md-2">
			            			<label for="department">Филиал:</label>
			            			@if(in_array(auth()->user()->id,[60,153]) || auth()->user()->hasRole('администратор'))
                                        <select name="department" id="department" class="form-control">
                                            <option value="ALL">ВЫБРАТЬ ВСЕ</option>
                                            @foreach($departments as $dep)
                                                <option value="{{ $dep->v_ext_ident }}" {{ $department == $dep->v_ext_ident ? 'selected' : '' }}>{{ $dep->v_name }}</option>
                                            @endforeach
                                        </select>
			            			@else
			            			<select name="department" class="form-control">
			            				<option value="{{ $departments->v_ext_ident }}" {{ $department == $departments->v_ext_ident }}>{{ $departments->v_name }}</option>
			            			</select>
			            			@endif
			            		</div>
			            	</div>
			            	<div class="row mt-4">
			            		<div class="col-md-2">
			            			<label>Дата от:</label>
					            	<input type="text" class="datepicker form-control" placeholder="Выберите дату" name="date_from" value="{{ $date_from }}">
			            		</div>
			            		<i class="mt-5 fas fa-minus"></i>
			            		<div class="col-md-2">
			            			<label>Дата до:</label>
					            	<input type="text" class="datepicker form-control" placeholder="Выберите дату" name="date_to" value="{{ $date_to }}" data-date-start-date="{{ $date_from }}">
			            		</div>
			            	</div>
		        		</form>
		            	<div class="mt-4">
		            		@if($date_from && $date_to)
			            	<button class="btn btn-success download-excel" data-type="1">Скачать детализацию ОС</button>
			            	<button class="btn btn-success download-excel" data-type="2">Скачать детализацию ТМЦ</button>
			            	<span class="text-muted" id="loading_spin" style="display:none;"><i class="fas fa-circle-notch fa-spin"></i> Ваш отчет генерируется, пожалуйста, не закрывайте окно.</span>
			            	@endif
		            	</div>
		            </div>
		            @if(auth()->user()->hasRole('администратор'))
		            <div class="tab-pane fade" id="consolidated_tab" role="tabpanel" aria-labelledby="consolidated_tab">
		            	<form action="">
		        			<div class="row mt-4">
			            		<div class="col-md-2">
			            			<label for="department">Филиал:</label>
			            			@if(auth()->user()->id == 153 || auth()->user()->hasRole('администратор'))
			            			<select name="department" id="department" class="form-control">
		            					<option value="ALL">ВЫБРАТЬ ВСЕ</option>
			            				@foreach($departments as $dep)
			            				<option value="{{ $dep->v_ext_ident }}" {{ $department == $dep->v_ext_ident ? 'selected' : '' }}>{{ $dep->v_name }}</option>
			            				@endforeach
			            			</select>
			            			@else
			            			<select name="department" class="form-control">
			            				<option value="{{ $departments->v_ext_ident }}" {{ $department == $departments->v_ext_ident }}>{{ $departments->v_name }}</option>
			            			</select>
			            			@endif
			            		</div>
			            		<div class="col-md-2">
			            			<label for="stock">Склад:</label>
			            			<select name="stock" id="stock" class="form-control">
				            			<option selected disabled>Не выбрано</option>
				            			@if(isset($stocks))
				            				@foreach($stocks as $st)
				            					<option value="{{ $st['id'] }}" {{ $stock == $st["id"] ? 'selected' : '' }}>{{ $st['name'] }}</option>
				            				@endforeach
			            				@endif
		            				</select>
			            		</div>
			            	</div>
			            	<div class="row mt-4">
			            		<div class="col-md-2">
			            			<label>Дата от:</label>
					            	<input type="text" class="datepicker form-control" placeholder="Выберите дату" name="date_from" value="{{ $date_from }}">
			            		</div>
			            		<i class="mt-5 fas fa-minus"></i>
			            		<div class="col-md-2">
			            			<label>Дата до:</label>
					            	<input type="text" class="datepicker form-control" placeholder="Выберите дату" name="date_to" value="{{ $date_to }}" data-date-start-date="{{ $date_from }}">
			            		</div>
			            	</div>
		        		</form>
		            	<div class="mt-4">
		            		@if($date_from && $date_to)
			            	<button class="btn btn-warning download-excel" data-type="3">Скачать сводный отчет ОС</button>
			            	<button class="btn btn-warning download-excel" data-type="4">Скачать сводный отчет ТМЦ</button>
			            	<span class="text-muted" id="loading_spin" style="display:none;"><i class="fas fa-circle-notch fa-spin"></i> Ваш отчет генерируется, пожалуйста, не закрывайте окно.</span>
			            	@endif
		            	</div>
					</div>
					@endif
					@if(auth()->user()->hasRole('администратор'))
					<div class="tab-pane fade" id="balance_tab" role="tabpanel" aria-labelledby="balance_tab">
						<form action="">
		        			<div class="row mt-4">
			            		<div class="col-md-2">
			            			<label for="department">Филиал:</label>
			            			@if(auth()->user()->id == 153 || auth()->user()->hasRole('администратор'))
			            			<select name="department" id="department" class="form-control">
			            				<option selected disabled>Выберите филиал</option>
		            					<!-- <option value="ALL">ВЫБРАТЬ ВСЕ</option> -->
			            				@foreach($departments as $dep)
			            				<option value="{{ $dep->v_ext_ident }}" {{ $department == $dep->v_ext_ident ? 'selected' : '' }}>{{ $dep->v_name }}</option>
			            				@endforeach
			            			</select>
			            			@else
			            			<select name="department" class="form-control">
			            				<option value="{{ $departments->v_ext_ident }}" {{ $department == $departments->v_ext_ident }}>{{ $departments->v_name }}</option>
			            			</select>
			            			@endif
			            		</div>
			            		<div class="col-md-2">
			            			<label for="stock">Склад:</label>
			            			<select name="stock" id="stock" class="form-control">
			            				<option selected disabled>Не выбрано</option>
				            			@if(isset($stocks))
				            				@foreach($stocks as $st)
				            					<option value="{{ $st['id'] }}" {{ $stock == $st["id"] ? 'selected' : '' }}>{{ $st['name'] }}</option>
				            				@endforeach
			            				@endif
		            				</select>
			            		</div>
			            	</div>
		        		</form>
		            	<div class="mt-4">
		            		@if($stock)
		            		<button class="btn btn-primary download-excel" data-type="5">Текущий остаток ОС</button>
		            		<button class="btn btn-primary download-excel" data-type="6">Текущий остаток ТМЦ</button>
		            		@endif
		            	</div>
					</div>
					@endif
				</div>
            </div>
            @if($rows && count($rows) > 0)
            <table class="table table-bordered">
				<tr>
					<td style="font-weight:bold;"><strong>Номер наряда</strong></td>
					<td style="font-weight:bold;"><strong>Серийный номер</strong></td>
					<td style="font-weight:bold;"><strong>Серийный номер комплекта</strong></td>
					<td style="font-weight:bold;"><strong>Город</strong></td>
					<td style="font-weight:bold;"><strong>Тип заказа</strong></td>
					<td style="font-weight:bold;"><strong>Откуда</strong></td>
					<td style="font-weight:bold;"><strong>Номер контракта</strong></td>
					<td style="font-weight:bold;"><strong>Дата работ</strong></td>
					<td style="font-weight:bold;"><strong>Дата передачи</strong></td>
				</tr>
				@foreach($rows as $row)
				<tr>
					<td>{{ $row->id_flow }}</td>
					<td>{{ $row->v_equipment_number }}</td>
					<td>{{ $row->v_serial }}</td>
					<td>{{ $row->town }}</td>
					<td>{{ $row->type }}</td>
					<td>{{ $row->name }}</td>
					<td>{{ $row->owner_id }}</td>
					<td>{{ $row->dt_plan_date }}</td>
					<td>{{ $row->created_at }}</td>
				</tr>
				@endforeach
			</table>
			@endif
        </div>
    </div>
@endsection

@section('page-scripts')
<script type="text/javascript">
	$(document).ready(function(){
		$('.download-excel').on('click', function(){
			let type = $(this).data('type');
			let file_name = 'Детализация ОС';
			if(type == 2){
				file_name = 'Детализация ТМЦ';
			}else if(type == 3){
				file_name = 'Сводный отчет ОС';
			}else if(type == 4){
				file_name = 'Сводный отчет ТМЦ';
			}else if(type == 5){
				file_name = 'Текущий остаток ОС';
			}else if(type == 6){
				file_name = 'Текущий остаток ТМЦ';
			}
			$('.download-excel').attr('disabled', 'disabled');
			$('#loading_spin').show();
			$.ajax({
				url: '/downloadReport',
				data: {
					type: type,
					date_from: $("input[name='date_from']").val(),
					date_to: $("input[name='date_to']").val(),
					department: $("select[name='department']").val(),
					stock: $("select[name='stock']").val()
				},
				xhrFields: {
		        	responseType: 'blob'
		        },
				success(data){
					var a = document.createElement('a');
		            var url = window.URL.createObjectURL(data);
		            a.href = url;
		            a.download = file_name + '.xlsx';
		            document.body.append(a);
		            a.click();
		            a.remove();
		            window.URL.revokeObjectURL(url);
		            $('#loading_spin').hide();
					$('.download-excel').removeAttr('disabled');
				},
				error(){
					alert('Возникла ошибка при генерации отчета!');
					$('#loading_spin').hide();
					$('.download-excel').removeAttr('disabled');
				}
			});
		});
		$("input[name='date_from'], input[name='date_to'], select").on("change", function(){
			$(this).closest("form").submit();
		});
		$("#myTab .nav-link").on("click", function(){
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
        $("#myTab").find(".nav-link[href='" + hash + "']").trigger('click');
	});
</script>
@endsection
