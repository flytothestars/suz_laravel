@extends('layouts.app')

@section('pageTitle', 'Панель администратора')

@section('content')
<div class="container-fluid mt--7">
    <div class="card min-height-500">
        <div class="card-body">
            <h1><i class="fas fa-crown text-yellow"></i> Панель супер-администратора</h1>
            <hr>
            <h2>SOAP-запросы в Forward</h2>
            <hr>
            @foreach($settings as $set)
                <label class="text-uppercase font-weight-bold d-block">{{ $set->name }}</label>
                <label class="custom-toggle">
                    <input type="checkbox" data-id="{{ $set->id }}" class="soap-setting" {{ $set->enabled ? 'checked' : '' }}>
                    <span class="custom-toggle-slider rounded-circle"></span>
                </label>
            @endforeach
        </div>
    </div>
</div>
@endsection

@section('page-scripts')
    <script type="text/javascript">
        $(document).ready(function(){
            $(".soap-setting").on("change", function(){
                let id = $(this).data("id");
                let enabled = ($(this).prop("checked") === true) ? 1 : 0;
                $.ajax({
                    url: "{{ route('admin.ajax.soap_settings_change') }}",
                    type: "POST",
                    data: {
                        _token: "{{ csrf_token() }}",
                        id: id,
                        enabled: enabled
                    },
                    success: function(){
                        $.growl.notice({ title: 'Уведомление', message: "Изменения сохранены" });
                    }
                });
            });
        });
    </script>
@endsection