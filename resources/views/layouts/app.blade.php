@php use Illuminate\Support\Facades\Auth; @endphp
    <!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('pageTitle')</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <!-- <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet"> -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:300,400,500,700,900&display=swap" rel="stylesheet">

    <!-- Styles -->
    <!-- Icons -->
    <link rel="shortcut icon" href="{{ asset('img/brand/favicon.png') }}" type="image/png">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.8.1/css/all.css"
          integrity="sha384-50oBUHEmvpQ+1lW4y57PTFmhCaXp0ML5d60M1M7uH2+nqUivzIebhndOJK28anvf" crossorigin="anonymous">
    <link type="text/css" href="{{ asset('css/jquery.growl.css') }}" rel="stylesheet">
    <link type="text/css" href="{{ asset('css/bootstrap-select.min.css') }}" rel="stylesheet">
    <link type="text/css" href="{{ asset('css/app.css?v=2.1') }}" rel="stylesheet">
    @yield('styles')
</head>
<body {{ $bodyClass ?? '' }}>
<div class="se-pre-con"></div>
@if(Auth::user())
    <!-- Sidenav -->
    <nav class="navbar navbar-vertical fixed-left navbar-expand-md navbar-light bg-white" id="sidenav-main">
        <div class="container-fluid">
            <!-- Toggler -->
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#sidenav-collapse-main"
                    aria-controls="sidenav-main" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Brand -->
            <a class="navbar-brand pt-0" href="/">
                <img src="{{ asset('img/brand/blue.png') }}" title="Система управления заявками"
                     class="navbar-brand-img" alt="...">
            </a>
            <div class="align-items-center my-3" style="width:100%">
                <div class="text-center text-md-center mb-0 font-weight-bold">{{ Auth::user()->name ?? 'Вход' }}</div>
                <div class="text-center">{{ Auth::user()->getRoleNames()->implode(', ') }}</div>
            </div>
            <hr class="my-3" style="width: 100%;">

            @if(Auth::user()->hasAnyRole(['администратор', 'диспетчер', 'кладовщик', 'техник', 'инспектор']))
                <form action="/search" style="width:100%;">
                    <input type="text" name="q" id="main_search_input" class="form-control" autofocus
                           placeholder="Поиск">
                </form>
            @endif

            <!-- Collapse -->
            <div class="collapse navbar-collapse" id="sidenav-collapse-main">
                <!-- Collapse header -->
                <div class="navbar-collapse-header d-md-none">
                    <div class="row">
                        <div class="col-12 collapse-close">
                            <button type="button" class="navbar-toggler" data-toggle="collapse"
                                    data-target="#sidenav-collapse-main" aria-controls="sidenav-main"
                                    aria-expanded="false" aria-label="Toggle sidenav">
                                <span></span>
                                <span></span>
                            </button>
                        </div>
                    </div>
                </div>
                <!-- Navigation -->
                <ul class="navbar-nav">
                    @if(Auth::user()->hasRole('администратор') || Auth::user()->hasRole('диспетчер') || Auth::user()->hasRole('инспектор'))
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('requests') ? 'active' : '' }}"
                               href="{{ route('requests') }}">
                                <i class="fas fa-list-ul text-red"></i> Список заявок
                            </a>
                        </li>
                    @endif
                    @if(Auth::user()->hasRole('администратор') || Auth::user()->hasRole('просмотр заявок'))
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('storyByGroup') ? 'active' : '' }}"
                               href="{{ route('storyByGroup') }}">
                                <i class="fas fa-list-ul text-yellow"></i> Заявки по группам
                            </a>
                        </li>
                    @endif
                    @if(Auth::user()->hasAnyRole(['администратор', 'диспетчер', 'просмотр маршрута', 'супервизер']))
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('routing') ? 'active' : '' }}"
                               href="{{ route('routing') }}">
                                <i class="fas fa-route text-success"></i> Маршрутизация
                            </a>
                        </li>
                    @endif
                    @if(Auth::user()->hasRole('техник'))
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('routelist') ? 'active' : '' }}" href="/routelist">
                                <i class="fas fa-list-ul text-orange"></i> Мой маршрутный лист
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('my_inventory') ? 'active' : '' }}" href="/my_inventory">
                                <i class="fas fa-toolbox text-yellow"></i> Мой инвентарь
                            </a>
                        </li>
                    @endif
                    @if(Auth::user()->hasAnyRole(['кладовщик', 'администратор']))
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('inventory') ? 'active' : '' }}" href="/inventory">
                                <i class="fas fa-warehouse text-info"></i>Инвентарь на складе
                            </a>
                        </li>

                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('report') ? 'active' : '' }}" href="/report">
                                <i class="fas fa-file-alt text-success"></i>Отчеты
                            </a>
                        </li>
                    @endif

                    @if(Auth::user()->id == 60) <!--TODO хардкод временный-->
                    <li class="nav-item">
                        <a class="nav-link {{ Request::is('report') ? 'active' : '' }}" href="/report">
                            <i class="fas fa-file-alt text-success"></i>Отчеты
                        </a>
                    </li>
                    @endif

                    @if(Auth::user()->hasRole(['диспетчер']) && Auth::user()->id != 60) <!--TODO хардкод временный-->
                        <li class="nav-item">
                            <a class="nav-link" href="/requests-report">
                                <i class="fas fa-file-alt text-orange"></i>Отчет по заявкам
                            </a>
                        </li>
                    @endif
                    @if(Auth::user()->hasAnyRole(['администратор', 'диспетчер', 'кладовщик']))
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('users') ? 'active' : '' }}" href="/users">
                                <i class="fas fa-users text-blue"></i> Пользователи
                            </a>
                        </li>
                    @endif
                    @if(Auth::user()->hasRole('супервизер'))
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('users') ? 'active' : '' }}" href="/users">
                                <i class="fas fa-users text-blue"></i> Техники
                            </a>
                        </li>
                    @endif
                    @if(Auth::user()->hasRole('супер-администратор'))
                        <li class="nav-item">
                            <a class="nav-link {{ Request::is('admin') ? 'active' : '' }}"
                               href="{{ route('admin.index') }}">
                                <i class="fas fa-crown text-orange"></i> Панель<br> супер-администратора
                            </a>
                        </li>
                    @endif
                    @if(Auth::user()->hasRole('администратор'))
                        <li class="nav-item">
                            <a href="/settings" class="nav-link {{ Request::is('settings') ? 'active' : '' }}"><i
                                    class="fas fa-cog text-primary"></i> Настройки</a>
                        </li>
                        <li class="nav-item">
                            <a href="http://suz-db.almatv.kz" class="nav-link" target="_blank"><i
                                    class="fas fa-database text-orange"></i> База данных</a>
                        </li>
                    @endif
                    @if(Auth::user()->id == 153)
                        <li class="nav-item">
                            <a href="/equipment" class="nav-link"><i class="fas fa-hdd text-orange"></i>
                                Оборудование</a>
                        </li>
                    @endif
                    <li class="nav-item">
                        <a href="/logout" class="nav-link"><i class="fas fa-power-off text-danger"></i> Выход</a>
                    </li>
                </ul>
                <div style="font-size:8pt;position:absolute;bottom:50px;min-width:80%;left:20px;right:0;color:#999;">
                    Время загрузки: {{ round((microtime(true) - LARAVEL_START), 2) }} с.
                </div>
            </div>
        </div>
    </nav>
@endif
<!-- Main content -->
<div class="main-content">
    @if(Auth::user())
        <!-- Top navbar -->
        <nav class="navbar navbar-top navbar-expand-md navbar-dark" id="navbar-main">
            <div class="container-fluid">
                <!-- Brand -->
                <a class="h4 mb-0 text-white text-uppercase d-none d-lg-inline-block" href="./index.html"></a>
            </div>
        </nav>
    @endif
    <!-- Header -->
    <div id="color_header"
         class="header bg-gradient-{{ Auth::user() != null ? Auth::user()->getColor() : 'primary' }} pb-7 pt-2 pt-md-8">
        <div class="container-fluid">
            @yield('top-content')
        </div>
    </div>
    <!-- Page content -->
    @if (session('status'))
        <div class="alert alert-success" role="alert">
            {{ session('status') }}
        </div>
    @endif
    @yield('content')
</div>

<!-- Core -->
<script type="text/javascript" src="{{ asset('vendor/jquery/dist/jquery.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('vendor/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('vendor/popper.js/dist/umd/popper.min.js') }}"></script>
<script type="text/javascript"
        src="{{ asset('vendor/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js') }}"></script>
<script type="text/javascript"
        src="{{ asset('vendor/bootstrap-datepicker/dist/locales/bootstrap-datepicker.ru.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('vendor/jquery-query-object/jquery.query-object.js') }}"></script>
<!-- Argon JS -->
<script type="text/javascript" src="{{ asset('js/argon.js?v=1.0.1') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery.growl.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/bootstrap-select.min.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/i18n/defaults-ru_RU.js') }}"></script>
<script type="text/javascript" src="{{ asset('js/jquery-ui.min.js') }}"></script>

{{--<script>--}}

{{--    // Callback function to execute when reCAPTCHA is verified--}}
{{--    function enableLoginButton() {--}}
{{--        loginButton.disabled = false;--}}
{{--    }--}}

{{--    function correctCaptcha(response) {--}}
{{--        var loginButton = document.getElementById('login');--}}

{{--        if (response !== '') {--}}
{{--            enableLoginButton();--}}
{{--        } else {--}}
{{--            loginButton.disabled = true;--}}
{{--        }--}}
{{--    }--}}

{{--    var loginButton = document.getElementById('login');--}}
{{--    var captcha = document.getElementById('recaptcha-agent');--}}

{{--    // Disable the login button by default--}}
{{--    if (captcha) {--}}
{{--        loginButton.disabled = true;--}}
{{--    }--}}

{{--</script>--}}

{{--<script src="https://www.google.com/recaptcha/api.js" async defer></script>--}}


@yield('page-scripts')
<script type="text/javascript">
    $(window).on('load', function () {
        $(".se-pre-con").fadeOut("slow");
    });
    $(document).ready(function () {
        setTimeout(function () {
            $('.alert-temporary').fadeOut();
        }, 5000);
        $('.modal-dialog').draggable({
            handle: ".modal-header"
        });
    });
</script>
</body>
</html>
