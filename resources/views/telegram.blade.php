@extends('layouts.app')

@section('content')
	@if(session('message'))
	<div class="row">
	    <div class="col-md-11 my-5 mx-5">
	        <div class="alert alert-danger">{{ session('message') }}</div>
	    </div>
	</div>
	@endif
	<div class="telegram-btn">
		<div class="my-5">
			Пожалуйста, авторизуйтесь через Telegram для доступа к Вашим заявкам.
		</div>
		<script async src="https://telegram.org/js/telegram-widget.js?19" data-telegram-login="{{env('TELEGRAM_BOT_NAME')}}" data-size="large" data-auth-url="{{ env('APP_URL') . '/telegram' }}" data-request-access="write"></script>
	</div>
	<style type="text/css">
		.telegram-btn{
			text-align: center;
			margin-top: 50px;
		}
	</style>
@endsection
