$(document).ready(function(){
    // Назначение заявок
    let draggable_options = {
        cursor: "grabbing",
        containment: '#main_container',
        cursorAt: {
            top: 5,
            left: 50
        },
        helper: function(){
            let id = $(this).data('request-id');
            return $("<div class='ui-widget-header'>Заявка №" + id + "</div>");
        }
    };
    $(".request-draggable").draggable(draggable_options);

    $(".droppable-cell").droppable({
        accept: ".request-draggable",
        classes: {
            "ui-droppable-active": "ui-state-active",
            "ui-droppable-hover": "ui-state-hover"
        },
        drop: function(event, ui){
            let spec_ci_flow = $(this).data("spec");
            let ci_flow = ui.draggable.data('ci-flow');
            let request_time = ui.draggable.data('time');
            if(request_time == $(this).data("time")){
                return false;
            }
            if(!spec_ci_flow.includes(ci_flow) && !ui.draggable.hasClass("dropped"))
            {
                if(!confirm("Тип заявки не соответствует типу специализации бригады, все равно продолжить?")){
                    return false;
                }
            }
            // если заявка уже назначена
            if(ui.draggable.hasClass("dropped")){
                if(!confirm("Заявка назначена на другое время. Вы точно хотите изменить время?")){
                    return false;
                }
                if(!spec_ci_flow.includes(ci_flow))
                {
                    if(!confirm("Тип заявки не соответствует типу специализации бригады, все равно продолжить?")){
                        return false;
                    }
                }
            }
            let id = ui.draggable.data('request-id');
            let routelist_id = $(this).parent().data("routelist-id");
            let date_time = $("input[name='date']").val() + " " + $(this).data("time");
            let old_time = $("input[name='date']").val() + " " + request_time;
            let csrf_token = $("input[name='_token']").val();
            $.ajax({
                url: "/request.assign",
                type: "POST",
                dataType: "json",
                data: {
                    _token: csrf_token,
                    request_id: id,
                    old_time: old_time,
                    routelist_id: routelist_id,
                    date_time: date_time
                },
                success: function(response){
                    alert(response['message']);
                    window.location.reload();
                },
                error: function(response){
                    alert("Ошибка: " + response['message']);
                }
            });
        }
    });

    $("input[name='date']").on("change", function(){
        let date = $(this).val();
        window.location.href = $.query.SET('date', date);
    });

    $('#installer_1').on('changed.bs.select', function(e, clickedIndex, isSelected, previousValue){
        let value = $(this).val();
        $("#installer_2 option").removeAttr("disabled");
        $("#installer_2 option[value=" + value + "]").attr('disabled', 'disabled');
    });
    $('#installer_2').on('changed.bs.select', function(e, clickedIndex, isSelected, previousValue){
        let value = $(this).val();
        $("#installer_1 option").removeAttr("disabled");
        $("#installer_1 option[value=" + value + "]").attr('disabled', 'disabled');
    });
    $('#edit_installer_1').on('changed.bs.select', function(e, clickedIndex, isSelected, previousValue){
        let value = $(this).val();
        if(value != 0){
            $("#edit_installer_2 option").removeAttr("disabled");
            $("#edit_installer_2 option[value=" + value + "]").attr('disabled', 'disabled');
        }
    });
    $('#edit_installer_2').on('changed.bs.select', function(e, clickedIndex, isSelected, previousValue){
        let value = $(this).val();
        if(value != 0) {
            $("#edit_installer_1 option").removeAttr("disabled");
            $("#edit_installer_1 option[value=" + value + "]").attr('disabled', 'disabled');
        }
    });

    $('#department_id').on('change', function(){
        let department_id = $(this).val();
        window.location.href = $.query.SET('department_id', department_id).REMOVE('location_id');
    });
    $('#location_id').on('change', function(){
        let location_id = $(this).val();
        window.location.href = $.query.SET('location_id', location_id);
    });
    $(".request-row").hover(function(){
        let id = $(this).data("request-id");
        $(".dropped[data-request-id='" + id +"']").toggleClass("accent");
    });

    $(".delete-routelist").on("click", function(){
        if(confirm("Вы действительно хотите удалить маршрут?")){
            let routelist_id = $(this).data("routelist-id");
            let csrf_token = $("input[name='_token']").val();
            $.ajax({
                url: "/routelist.destroy",
                type: "POST",
                data: {
                    _token: csrf_token,
                    id: routelist_id
                },
                success(response){
                    alert(response['message']);
                    window.location.reload();
                }
            });
        }
    });
    $(".installers-td").on("click", function(){
        $(this).toggleClass("active");
    });
    $(".edit-routelist").on("click", function(){
        let routelist_id = $(this).data("routelist-id");
        let date = $("input[name='date']").val();
        let location_id = $("select[name='location_id']").val();
        let department_id = $("select[name='department_id']").val();
        $("#edit_routelist_id").val(routelist_id);
        $.ajax({
            url: '/get_routelist_installers_as_options',
            data: {
                date: date,
                routelist_id: routelist_id,
                location_id: location_id,
                department_id: department_id
            },
            success(response){
                response = JSON.parse(response);
                console.log(response);
                $("#edit_installer_1").html(response[0]['options']).selectpicker('val', response[0]['val']).selectpicker('refresh');
                if(response[1] != undefined){
                    $("#edit_installer_2").html(response[1]['options']).selectpicker('val', response[1]['val']).selectpicker('refresh');
                }
                $("#edit_installer_2 option").removeAttr("disabled");
                $("#edit_installer_2 option[value=" + response[0]['val'] + "]").attr('disabled', 'disabled');
                $("#edit_installer_1, #edit_installer_2").on("changed.bs.select", function(){
                    $("#editRouteListModal button[type=submit]").removeAttr("disabled");
                });
            }
        });
        $("#editRouteListModal").modal();
    });
});