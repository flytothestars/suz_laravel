@extends('layouts.app')

@section('pageTitle', 'Отчет по заявкам')

@php
	$selected_department = isset($_GET['department']) ? $_GET['department'] : '';
	$selected_date_from = isset($_GET['date_from']) ? $_GET['date_from'] : date('Y-m-d', strtotime('now -1 day'));
    $selected_date_to = isset($_GET['date_to']) ? $_GET['date_to'] : date('Y-m-d');
	$selected_ci_flow = isset($_GET['type']) ? $_GET['type'] : '';
	$selected_n = isset($_GET['n']) ? $_GET['n'] : '';
@endphp

@section('content')
	<div class="container-fluid mt--7">
    <div class="card min-height-500 mb-5">
        <div class="card-body">
            <h1>Отчет по заявкам</h1>
        	<form action="">
        		<div class="form-row">
	            	<div class="form-group col-md-2">
	            		<label for="filter_department">Филиал:</label>
	            		<select name="department" class="form-control" id="filter_department">
	            			<option disabled selected>Выберите филиал</option>
	            			@foreach($departments as $dep)
	            			<option value="{{ $dep->v_ext_ident }}" {{ $dep->v_ext_ident == $selected_department ? 'selected' : '' }}>{{ $dep->v_name }}</option>
	            			@endforeach
	            		</select>
	            	</div>
	            	<div class="form-group col-md-2">
                        <label for="filter_date_from">Даты получения:</label>
                        <div class="form-group">
                            <input class="form-control datepicker" id="filter_date_from" name="date_from" placeholder="Start date" type="text" value="{{ $selected_date_from }}">
                        </div>
                    </div>
                    <div class="form-group col-md-2">
                        <label for="filter_date_to">&nbsp;</label>
                        <div class="form-group">
                            <input class="form-control datepicker" id="filter_date_to" name="date_to" placeholder="End date" type="text" value="{{ $selected_date_to }}">
                        </div>
                    </div>
	            	<div class="form-group col-md-2">
	            		<label for="filter_type">Тип заказа:</label>
	            		<select name="type" class="form-control" id="filter_type">
	            			<option disabled selected>Выберите тип</option>
	            			@foreach($types as $type)
	            			<option value="{{ $type->id_ci_flow }}" {{ $type->id_ci_flow == $selected_ci_flow ? 'selected' : '' }}>{{ $type->v_name }}</option>
	            			@endforeach
	            		</select>
	            	</div>
	            	<!-- <div class="form-group col-md-2">
	            		<label for="filter_n">Строк на страницу:</label>
	            		<select name="n" class="form-control" id="filter_n">
	            			<option selected>5</option>
	            			<option {{ $selected_n == 10 ? 'selected' : '' }}>10</option>
	            			<option {{ $selected_n == 15 ? 'selected' : '' }}>15</option>
	            			<option {{ $selected_n == 20 ? 'selected' : '' }}>20</option>
	            			<option {{ $selected_n == 50 ? 'selected' : '' }}>50</option>
	            			<option {{ $selected_n == 100 ? 'selected' : '' }}>100</option>
	            		</select>
	            	</div> -->
	            	<div class="form-group">
	            		<label>&nbsp;</label><br>
	            		<button class="btn btn-primary" id="report_submit">Применить</button>
	            	</div>
	            	<span class="text-muted" id="loading_spin" style="display:none;"><i class="fas fa-circle-notch fa-spin"></i> Ваш отчет генерируется, пожалуйста, не закрывайте окно.</span>
	            </div>
    		</form>
            <!-- @if(isset($requests) && $requests->count() > 0)
            <div class="table-responsive mt-4">
            	<div class="text-right">
            		<button class="btn btn-success exportToExcel">Выгрузить в Excel</button>
            	</div>
            	<div class="my-3">
            		<div>(ограничение: 8000 заявок на страницу)</div>
		            {{ $requests->appends(request()->input())->links() }}
	            </div>
	            <table class="table table-bordered mt-4" id="table">
	            	<thead>
	            		<th>Номер наряда</th>
	            		<th>Тип заказа</th>
	            		<th>Дата создания заказа</th>
	            		<th>Дата завершения заказа</th>
	            		<th>Номер контракта</th>
	            		<th>Филиал</th>
	            		<th>Наименование участка</th>
	            		<th>Адрес клиента</th>
	            		<th>Статус заказа</th>
	            		<th>Номер сектора</th>
	            		<th>Категория контракта</th>
	            		<th>Тип работы</th>
	            		<th>Технология услуг</th>
						<th>Классификация ремонта</th>
	            		<th>Принятые меры (комментарий техника)</th>
	            		<th>Плановая дата</th>
	            		<th>Завершивший заказ</th>
	            		<th>Техник 1</th>
	            		<th>Техник 2</th>
	            	</thead>
	            	<tbody>
	            		@foreach($requests as $request)
	            		<tr>
	            			<td>{{ $request->id_flow }}</td>
	            			<td>{{ $request->ci_flow }}</td>
	            			<td>{{ $request->dt_start }}</td>
	            			<td>{{ $request->dt_stop }}</td>
	            			<td>{{ $request->v_contract }}</td>
	            			<td>{{ $request->department }}</td>
	            			<td>{{ $request->location }}</td>
	            			<td>{{ $request->town . ', ' . $request->street . ', ' . $request->house . ', ' . $request->flat }}</td>
	            			<td>{{ $request->status }}</td>
	            			<td>{{ $request->sector }}</td>
	            			<td>{{ $request->product }}</td>
	            			<td>{{ $request->kind_works }}</td>
	            			<td>
	            				@if($request->service)
		            				@foreach($request->service as $key => $service)
		            				{{ $service->name .': '. $service->technology . '; ' }}
		                            @endforeach
	                            @endif
	            			</td>
							<td>
								@foreach($request->repairtypes as $type)
									{{ $type . '; ' }}
            					@endforeach
							</td>
	            			<td>{{ $request->comment }}</td>
	            			<td>{{ $request->dt_plan_date }}</td>
	            			<td>{{ $request->completing->name }}</td>
            				@foreach($request->installers as $installer)
	            				<td>{{ $installer }}</td>
            				@endforeach
	            		</tr>
	            		@endforeach
	            	</tbody>
	            </table>
	            <div class="my-3">
		            {{ $requests->appends(request()->input())->links() }}
	            </div>
            </div>
            @else
				<div class="mt-4">Нет заявок. Выберите в фильтрах.</div>
        	@endif -->
        </div>
    </div>
@endsection

@section('page-scripts')
<script src="{{ asset('js/jquery.table2excel.js') }}"></script>
<script type="text/javascript">
	$(document).ready(function(){
		// $(".exportToExcel").click(function(e){
		// 	var table = $('#table');
		// 	if(table && table.length){
		// 		var preserveColors = (table.hasClass('table2excel_with_colors') ? true : false);
		// 		$(table).table2excel({
		// 			exclude: ".noExl",
		// 			name: "Отчет по заявкам",
		// 			filename: "requests_report_" + new Date().toISOString().replace(/[\-\:\.]/g, "") + ".xls",
		// 			fileext: ".xls",
		// 			exclude_img: true,
		// 			exclude_links: true,
		// 			exclude_inputs: true,
		// 			preserveColors: preserveColors
		// 		});
		// 	}
		// });
		$('#report_submit').on('click', function(){
			if($("select[name='department']").val() == null || $("select[name='type']").val() == null)
			{
				alert('Выберите филиал и тип заказа!');
				return false;
			}
			else
			{
				let file_name = 'Отчет по заявкам';
				$('#report_submit').attr('disabled', 'disabled');
				$('#loading_spin').show();
				$.ajax({
					url: '/downloadRequests',
					data: {
						date_from: $("input[name='date_from']").val(),
						date_to: $("input[name='date_to']").val(),
						department: $("select[name='department']").val(),
						type: $("select[name='type']").val()
					},
					xhrFields: {
						responseType: 'blob'
					},
					success(data){
						var binaryData = [];
						binaryData.push(data);
						var a = document.createElement('a');
						var url = window.URL.createObjectURL(new Blob(binaryData, {type: "application/xlsx"}));
						a.href = url;
						a.download = file_name + '.xlsx';
						document.body.append(a);
						a.click();
						a.remove();
						window.URL.revokeObjectURL(url);
						$('#loading_spin').hide();
						$('#report_submit').removeAttr('disabled');
						window.location.href = "/requests-report";
					},
					error(){
						alert('Возникла ошибка при генерации отчета!');
						$('#loading_spin').hide();
						$('#report_submit').removeAttr('disabled');
					}
				});
			}
		});
	});
</script>
@endsection