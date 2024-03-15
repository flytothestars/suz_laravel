@extends('layouts.app')

@section('pageTitle', 'Оборудование')

@php
	$nameArr = explode(" ", \Auth::user()->name);
	$name = $nameArr[1];
@endphp

@section('content')
<input type="hidden" name="current_name" value="{{ $name }}">
<div class="container-fluid mt--7">
	<div class="card min-height-500">
		<div class="card-body py-5">
			<h1 class="display-3 text-center">Оборудование</h1>
			@if(auth()->user()->hasRole('администратор'))
			<div class="text-center mb-3">Здесь вы можете найти комплект по серийному номеру, удалить дубликаты, сменить владельца, сменить склад</div>
			@else
			<div class="text-center mb-3">Здесь вы можете найти комплект по серийному номеру, сменить владельца, сменить склад</div>
			@endif
			<div class="row justify-content-center">
				<div class="col-md-4">
					<input type="text" class="form-control text-center" id="search_equipment" placeholder="Введите серийный номер комплекта">
					<div class="text-center mt-3" id="search_gif" style="display:none;">
						<img src="{{ asset('/img/loading.gif') }}" width="32"> Поиск ...
					</div>
				</div>
			</div>
			<div id="results" class="mb-5"></div>
		</div>
	</div>
	<!-- Modal -->
	<div class="modal fade" id="changeEquipmentOwnerModal" tabindex="-1" role="dialog" aria-labelledby="changeEquipmentOwnerModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-body">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
					<input type="hidden" name="kit_id">
					<label for="owner">Выберите техника:</label>
					<select name="owner" id="owner" class="form-control selectpicker" data-live-search="true">
						@foreach($owners as $owner)
						<option value="{{ $owner->id }}">{{ $owner->name }}</option>
						@endforeach
					</select>
					<button class="btn btn-success mt-5" id="change_equipment_owner_btn">Сделать владельцем</button>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade" id="changeEquipmentStockModal" tabindex="-1" role="dialog" aria-labelledby="changeEquipmentStockModalLabel" aria-hidden="true">
		<div class="modal-dialog modal-dialog-centered" role="document">
			<div class="modal-content">
				<div class="modal-body">
					<button type="button" class="close" data-dismiss="modal" aria-label="Close">
						<span aria-hidden="true">&times;</span>
					</button>
					<input type="hidden" name="kit">
					<label for="stock">Выберите склад:</label>
					<select name="stock" id="stock" class="form-control selectpicker" data-live-search="true">
						@foreach($stocks as $stock)
						<option value="{{ $stock->id }}">{{ $stock->name }}</option>
						@endforeach
					</select>
					<button class="btn btn-success mt-5" id="change_equipment_stock_btn">Сменить склад</button>
				</div>
			</div>
		</div>
	</div>
</div>
@endsection

@section('page-scripts')
<script>
	$(document).ready(function(){
		$("#search_equipment").on("keyup", function(e){
			if(e.which == 13){
				$.ajax({
					url: "/search_equipment",
					data: {
						query: $(this).val()
					},
					beforeSend(){
						$("#search_gif").show();
					},
					dataType: "json",
					success(response){
						$("#results").html(response['html']);
						$("#search_gif").hide();
					},
					error(){
						$("#search_gif").hide();
					}
				});
			}
		});
		$("body").on("click", ".delete-btn", function(){
			let id = $(this).data('id');
			let name = $("input[name=current_name]").val();
			if(confirm(name + ', вы уверены? Вы удалите этим все комплектующие данного комплекта. Перепроверьте всё! Если уверены, жмите "Ок".')){
				$.ajax({
					url: "/delete_equipment",
					type: "POST",
					dataType: "json",
					data: {
						_token: $("meta[name=csrf-token]").attr("content"),
						id: id
					},
					success(response){
						alert(response['html']);
						window.location.reload();
					}
				});
			}
		});
		$("body").on("click", ".move-btn", function(){
			$("#changeEquipmentOwnerModal").find("input[name=kit_id]").val($(this).data('id'));
			$("#changeEquipmentOwnerModal").modal('show');
		});
		$("body").on("click", ".stock-btn", function(){
			$("#changeEquipmentStockModal").find("input[name=kit]").val($(this).data('id'));
			$("#changeEquipmentStockModal").modal('show');
		});

		$("body").on("click", "#change_equipment_owner_btn", function(){
			let id = $("input[name=kit_id]").val();
			let name = $("input[name=current_name]").val();
			let owner_id = $("select[name=owner]").val();
			if(confirm(name + ', вы уверены? Вы передадите этот комплект со всеми его комплектующими другому. Перепроверьте всё! Если уверены, жмите "Ок".')){
				$.ajax({
					url: "/change_equipment_owner",
					type: "POST",
					dataType: "json",
					data: {
						_token: $("meta[name=csrf-token]").attr("content"),
						id: id,
						owner_id: owner_id
					},
					success(response){
						alert(response['html']);
						window.location.reload();
					}
				});
			}
		});
		$("body").on("click", "#change_equipment_stock_btn", function(){
			let id = $("input[name=kit]").val();
			let name = $("input[name=current_name]").val();
			let stock_id = $("select[name=stock]").val();
			if(confirm(name + ', вы уверены? Вы передадите этот комплект со всеми его комплектующими на выбранный склад. Перепроверьте всё! Если уверены, жмите "Ок".')){
				$.ajax({
					url: "/change_equipment_stock",
					type: "POST",
					dataType: "json",
					data: {
						_token: $("meta[name=csrf-token]").attr("content"),
						id: id,
						stock_id: stock_id
					},
					success(response){
						alert(response['html']);
						window.location.reload();
					}
				});
			}
		});
	});
</script>
@endsection