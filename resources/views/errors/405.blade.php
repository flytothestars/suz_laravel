@extends('errors.illustrated-layout')

@section('code', '405')
@section('title', 'Неверная ссылка')

@section('image')
<div style="background-image: url({{ asset('/svg/403.svg') }});" class="absolute pin bg-cover bg-no-repeat md:bg-left lg:bg-center"></div>
@endsection

@section('message', 'Неверная ссылка. Вам нельзя быть на этой странице.')
