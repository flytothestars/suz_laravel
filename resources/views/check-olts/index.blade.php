@extends('layouts.app')

@php
    $pageTitle = 'Расходные материалы';
    $selectedType = isset($_GET['type']) ? $_GET['type'] : '';
@endphp

@section('top-content')
    <a href="/settings" class="btn btn-white top-back-btn"><i class="fas fa-arrow-left"></i> Вернуться в настройки</a>
@endsection

@section('pageTitle', $pageTitle)

@section('content')
    <div class="container-fluid mt--7">
        <div class="card min-height-500">
            <form action="/check-olts" method="post" enctype="multipart/form-data">
                @csrf
                <input type="file" name="file">
                <button type="submit">Загрузить</button>
            </form>
            @isset($data)
                <table class="table">
                    <thead>
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">hos_name_olt</th>
                        <th scope="col">ip_address</th>
                        <th scope="col">status</th>
                    </tr>
                    </thead>
                    <tbody>
                    @php $i = 1; @endphp
                    @foreach($data as $k => $v)
                        @if( $k !== 'query_string')
                            <tr>
                                <th scope="row">{{ $i }}</th>
                                <td>{{ $k }}</td>
                                <td>{{ $v->ip_address }}</td>
                                <td>{{ $v->status }}</td>
                            </tr>
                        @endif
                        @php $i++; @endphp
                    @endforeach
                    </tbody>
                </table>
            @endisset
        </div>
    </div>
@endsection
