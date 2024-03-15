@php
    $bodyClass = "class=bg-default";
@endphp

@extends('layouts.app')

@section('styles')
<style>
    #color_header{
        display: none;
    }
</style>
@endsection

@section('content')
<div class="main-content">
    <!-- Header -->
    <div class="header bg-gradient-primary py-7 py-lg-8">
        <div class="container">
            <div class="header-body text-center mb-7">
                <div class="row justify-content-center">
                    <div class="col-lg-5 col-md-6">
                        <h1 class="text-white">Добро пожаловать в СУЗ!</h1>
                    </div>
                </div>
            </div>
        </div>
        <div class="separator separator-bottom separator-skew zindex-100">
            <svg x="0" y="0" viewBox="0 0 2560 100" preserveAspectRatio="none" version="1.1" xmlns="http://www.w3.org/2000/svg">
                <polygon class="fill-default" points="2560 0 2560 100 0 100"></polygon>
            </svg>
        </div>
    </div>
    <!-- Page content -->
    <div class="container mt--8 pb-5">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-7">
                <div class="card bg-secondary shadow border-0">
                    @if(session('message'))
                        <div class="my-4 alert alert-success alert-temporary">{{ session('message') }}</div>
                    @endif
                    <div class="card-header bg-transparent">
                        <div class="text-muted text-center my-0">Вход в систему</div>
                    </div>
                    <div class="card-body px-lg-5 py-lg-5">
                        <form id="loginForm" method="POST" action="{{ route('login') }}">
                            @csrf
                            <div class="form-group mb-3">
                                <input id="email" type="text" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" required autofocus>
                                @if ($errors->has('email'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="form-group">
                                <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" required>
                                @if ($errors->has('password'))
                                    <span class="invalid-feedback" role="alert">
                                        <strong>{{ $errors->first('password') }}</strong>
                                    </span>
                                @endif
                            </div>
                            <div class="custom-control custom-control-alternative custom-checkbox">
                                <input class="custom-control-input form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="remember">
                                    <span class="text-muted">Запомнить меня</span>
                                </label>
                            </div>
                            <div class="text-center">
                                <button id="login" type="submit" class="btn btn-primary my-4" @if(!app()->environment('dev') && !app()->environment('stage')) disabled @endif>Войти</button>
                            </div>
                            @if(!app()->environment('dev') && !app()->environment('stage'))
                                <div class="form-group text-center">
                                    @include('auth.captcha')
{{--                                    <div id="recaptcha-agent" class="g-recaptcha" data-sitekey="6LdspA8UAAAAAMIdeMCBakhN0yGr4CHqgdHI_h7p" data-callback="correctCaptcha"></div>--}}
                                </div>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-scripts')
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const form = document.getElementById("loginForm");
            form.addEventListener("submit", function () {
                const emailInput = form.elements['email'];

                if (!emailInput.value.includes("@almatv.kz")) {
                    emailInput.value += "@almatv.kz"
                }
            })
        })
    </script>
@endsection
