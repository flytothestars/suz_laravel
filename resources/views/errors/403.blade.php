@extends('errors.illustrated-layout')

@section('code', '403')
@section('title', __('Forbidden'))

@section('image')
<div style="background-image: url({{ asset('/svg/403.svg') }});" class="absolute pin bg-cover bg-no-repeat md:bg-left lg:bg-center"></div>
@endsection

@section('message', 'У вас нет доступа к этой странице.')
