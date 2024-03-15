@extends('layouts.app')

@php
    $department_id = isset($_GET['department_id']) ? $_GET['department_id'] : null;
    $location_id = isset($_GET['location_id']) ? $_GET['location_id'] : null;
    $date = isset($_GET['date']) ? $_GET['date'] : date("Y-m-d");
    $sort_order = isset($_GET['sort_order']) && $_GET['sort_order'] == 'asc' ? 'desc' : 'asc';
@endphp

@section('pageTitle', 'Маршрутные листы')

@section('top-content')
    <div class="row mt-3 mb-5">
        <div class="col-md-12">
            <div class="card shadow">
                <div class="card-header">
                    <h1 class="my-0">Маршрутизация</h1>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-2">
                            <label for="department_id">Филиал:</label>
                            @if(auth()->user()->hasRole('супервизер') && !auth()->user()->hasAnyRole(['администратор', 'диспетчер', 'просмотр маршрута']))
                            <select name="department" id="department" class="form-control" data-style="btn-secondary" data-live-search="true">
                                <option value="{{ $departments->id }}">{{ $departments->name }}</option>
                            </select>
                            @else
                            <select name="department_id" id="department_id" class="form-control" data-style="btn-secondary" data-live-search="true">
                                <option selected disabled>Не выбрано</option>
                                @foreach($departments as $dep)
                                    <option value="{{ $dep->id_department }}" {{ $department_id == $dep->id_department ? 'selected' : '' }}>{{ $dep->v_name }}</option>
                                @endforeach
                            </select>
                            @endif
                        </div>
                        <div class="col-md-2">
                            <label for="location_id">Участок:</label>
                            @if(auth()->user()->hasRole('супервизер') && !auth()->user()->hasAnyRole(['администратор', 'диспетчер', 'просмотр маршрута']))
                            <select name="location" id="location" class="form-control" data-style="btn-secondary" data-live-search="true">
                                <option value="{{ $locations->id }}">{{ $locations->v_name }}</option>
                            </select>
                            @else
                            <select name="location_id" id="location_id" class="form-control" data-style="btn-secondary" data-live-search="true">
                                <option selected disabled>Не выбрано</option>
                                @if(isset($locations))
                                    @foreach($locations as $loc)
                                        <option value="{{ $loc->id_location }}" {{ $location_id == $loc->id_location ? 'selected' : '' }}>{{ $loc->v_name }}</option>
                                    @endforeach
                                @endif
                            </select>
                            @endif
                        </div>
                        <div class="col-md-2">
                            <label>Дата:</label>
                            <input type="text" class="form-control datepicker" name="date" value="{{ $date }}">
                        </div>
                    </div>
                    @if($errors->any())
                        <div class="mt-4 alert alert-danger">
                            <h5 class="text-white">У вас есть ошибки в форме:</h5>
                            <ul class="m-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@section('content')
    <div class="container-fluid mt--7" id="main_container">
        @if(!Auth::user()->hasRole('просмотр маршрута'))
        <div class="row">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header">Заявки {{ isset($requests) ? "(" . $requests->count() . ")" : '' }}</div>
                    <div class="table-responsive" style="max-height: 500px">
                        <table class="table align-items-center table-flush" id="route_requests_table">
                            <thead class="thead-light">
                                <th onclick="sortTable(0)" class="cursor-pointer">№  <i class="fas fa-sort"></i></th>
                                <th></th>
                                <th onclick="sortTable(2)" class="cursor-pointer">Статус <i class="fas fa-sort"></i></th>
                                <th onclick="sortTable(3)" class="cursor-pointer">Сектор <i class="fas fa-sort"></i></th>
                                <th onclick="sortTable(4)" class="cursor-pointer">Улица <i class="fas fa-sort"></i></th>
                                <th onclick="sortTable(5)" class="cursor-pointer">Дом <i class="fas fa-sort"></i></th>
                                <th onclick="sortTable(6)" class="cursor-pointer">Квартира <i class="fas fa-sort"></i></th>
                                <th onclick="sortTable(7)" class="cursor-pointer">Тип <i class="fas fa-sort"></i></th>
                            </thead>
                            <tbody>
                            @if(isset($requests))
                                @if(count($requests) == 0)
                                    <tr>
                                        <td colspan="8" class="text-center">Нет заявок.</td>
                                    </tr>
                                @endif
                                @foreach($requests as $req)
                                    <tr onclick="selectRequest(this)" data-coordinates="{{ json_encode($req->coordinates) }}" data-basic-coordinates="{{ json_encode($req->basic_coordinates) }}" class="request-row{{ $req->status != 'Назначено' ? ' request-draggable' : '' }}" data-request-id="{{ $req->id }}" data-ci-flow="{{ $req->id_ci_flow }}">
                                        <td><a href="/requests/{{ $req->id }}" class="btn-link">{{ $req->id }}</a></td>
                                        <td>{!! $req->status != 'Назначено' ? '<i class="fas fa-arrows-alt"></i>' : '' !!}</td>
                                        <td><span class="font-weight-bold text-{{ getStatusClass($req->status) }}">{{ $req->status }}</span></td>
                                        <td>{{ $req->sector }}</td>
                                        <td>{{ $req->street }}</td>
                                        <td>{{ $req->house }}</td>
                                        <td>{{ $req->v_flat }}</td>
                                        <td>{{ $req->ci_flow }}</td>
                                    </tr>
                                @endforeach
                            @elseif(isset($_GET['department_id']))
                                <tr>
                                    <td colspan="7" class="text-center">Выберите участок</td>
                                </tr>
                            @else
                                <tr>
                                    <td colspan="7" class="text-center">Выберите филиал</td>
                                </tr>
                            @endif
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card shadow">
                    <div id="map" style="width: 100%; height: 500px"></div>
                </div>
            </div>
        </div>
        @endif
        <div class="row my-5">
            <div class="col-md-12">
                <div class="card shadow">
                    <div class="card-header">
                        <h2 class="my-0">Маршруты</h2>
                    </div>
                    <div class="table-responsive routes-table">
                        <table class="table table-bordered align-items-center" id="routelist">
                            <thead class="thead-light">
                                <th></th>
                                <th>Бригады</th>
                                @foreach($time_intervals as $ti)
                                    <th>{{ date("H:i", $ti) }}</th>
                                @endforeach
                            </thead>
                            <tbody>
                            @if(isset($route_lists))
                                @foreach($route_lists as $route_list)
                                    <tr class="rowToToggle" data-routelist-id="{{ $route_list->id }}">
                                        <td class="bg-light-2 routelist-controls-td">
                                            @if(!Auth::user()->hasRole('просмотр маршрута'))
                                                @if(!$route_list->getRouteListRequest())
                                                <button data-toggle="tooltip" data-placement="top" title="Удалить маршрут"
                                                        class="btn btn-secondary btn-sm delete-routelist"
                                                        data-routelist-id="{{ $route_list->id }}"><i class="fas fa-times-circle text-danger"></i></button>
                                                @endif

                                                <button data-toggle="tooltip" data-placement="top" title="Изменить маршрут"
                                                        class="btn btn-secondary btn-sm edit-routelist"
                                                        data-routelist-id="{{ $route_list->id }}">
                                                    <i class="fas fa-pen"></i>
                                                </button>

                                               <button class="btn btn-secondary btn-sm toggleButton">
                                                   <i class="fas fa-eye-slash"></i>
                                               </button>
                                            @endif

                                            @if($route_list->getSpec() != '')
                                                @foreach($route_list->getSpec() as $spec)
                                                    <div class="form-control form-control-sm mt-1" type="text">{{ $spec->v_name }}</div>
                                                @endforeach
                                            @endif

                                        </td>
                                        <td class="installers-td" title="Маршрутный лист №{{ $route_list->id }}" onclick="selectRoute({{ $route_list->id }})" data-toggle="tooltip" data-placement="right" data-routelist_id="{{ $route_list->id }}">
                                            @foreach($route_list->installers() as $installer)
                                                <div>{{ $installer->name }}</div>
                                            @endforeach
                                        </td>

                                        @foreach($time_intervals as $ti)
                                            @php
                                                $current_time = $date . " " . date("H:i", $ti);
                                                $assigned_requests = $route_list->getRequestsByDatetime($current_time);
                                                $assigned_requests_classes = '';
                                            @endphp
                                            <td data-time="{{ date('H:i', $ti) }}" data-spec="{{ $route_list->getSpecCiFlow() }}" class="droppable-cell">
                                                @if($assigned_requests)
                                                    @foreach($assigned_requests as $assigned)
                                                        <div data-time="{{ date('H:i', $ti) }}" data-ci-flow="{{ $assigned->id_ci_flow }}" data-request-id="{{ $assigned->id }}" class="dropped dropped-{{ $assigned->id_ci_flow == '2404' && date('H:i', $ti) < date('H:i') && $date == date('Y-m-d', strtotime('today')) && $assigned->getStatus()->id == 4 ? 'repair' : ($assigned->id_ci_flow == '2402' && date('H:i', $ti) < date('H:i') && $date == date('Y-m-d', strtotime('today')) && $assigned->getStatus()->id == 4 ? 'setup' : $assigned->getStatus()->class) }} {{ !in_array($assigned->getStatus()->id, [3,5]) && Auth::user()->hasAnyRole('администратор|диспетчер|супервизер') ? ' request-draggable' : '' }} request-cell px-3 py-1" onclick="window.open('{{env('APP_URL')}}/requests/{{ $assigned->id }}','_blank');">
                                                            {{ $assigned->id }}
                                                        </div>
                                                    @endforeach
                                                @endif
                                        @endforeach
                                    </tr>

                                    <tr class="fromToggle" style="display: none; text-align: center;">
                                        <td colspan="2">
                                            <button class="btn btn-secondary btn-sm toggleFromButton">
                                                @foreach($route_list->installers() as $installer)
                                                    {{ $installer->name . '; ' }}
                                                @endforeach
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                            </tbody>
                        </table>
                    </div>
                    @if($date >= date('Y-m-d') && !Auth::user()->hasAnyRole(['просмотр маршрута', 'супервизер']))
                    <div class="card-footer">
                        <div class="text-center my-4">
                            <button class="btn btn-primary" {{ !isset($_GET['location_id']) || count($requests) == 0 ? 'disabled' : '' }} data-toggle="modal" data-target="#addRouteModal">Добавить маршрут</button>
                        </div>
                    </div>
                    @elseif($date >= date('Y-m-d') && Auth::user()->hasRole('супервизер'))
                    <div class="card-footer">
                        <div class="text-center my-4">
                            <button class="btn btn-primary" data-toggle="modal" data-target="#addRouteModal">Добавить маршрут</button>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- addRouteModal -->
    <div class="modal fade" id="addRouteModal" tabindex="-1" role="dialog" aria-labelledby="addRouteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addRouteModalLabel">Добавление маршрута</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('routelist.store') }}" method="post">
                    @csrf
                    <div class="modal-body border-top">
                        <input type="hidden" name="location_id" value="{{ isset($_GET['location_id']) ? $_GET['location_id'] : '' }}">
                        <input type="hidden" name="date" value="{{ $date }}">
                        <div class="form-group">
                            <label for="installer_1">Монтажник 1:</label>
                            <select name="installer_1" id="installer_1" class="form-control selectpicker" data-style="btn-secondary" data-live-search="true" required>
                                <option selected disabled>Не выбрано</option>
                                @if(isset($installers))
                                    @foreach($installers as $in)
                                        <option value="{{ $in->id }}">{{ $in->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="installer_2">Монтажник 2:</label>
                            <select name="installer_2" id="installer_2" class="form-control selectpicker" data-style="btn-secondary" data-live-search="true">
                                <option selected disabled>Не выбрано</option>
                                @if(isset($installers))
                                    @foreach($installers as $in)
                                        <option value="{{ $in->id }}">{{ $in->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="specialization">Специализация (обязательно):</label><br>
                            @if(isset($specialization))
                                @foreach($specialization as $spec)
                                    <input type="checkbox" name="specialization[]" value="{{ $spec->id_spec }}">&nbsp;{{ $spec->v_name }}<br>
                                @endforeach
                            @endif
                        </div>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="submit" onclick="validateForm()" class="btn btn-primary">Добавить</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- editRouteListModal -->
    <div class="modal fade" id="editRouteListModal" tabindex="-1" role="dialog" aria-labelledby="editRouteListModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editRouteListModalLabel">Изменение маршрута</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('routelist.update') }}" method="post">
                    <div class="modal-body">
                        @csrf
                        <input type="hidden" name="routelist_id" id="edit_routelist_id">
                        <h4>Выберите новых монтажников:</h4>
                        <div class="form-group">
                            <label for="edit_installer_1">Монтажник 1:</label>
                            <select name="edit_installer_1" id="edit_installer_1" class="form-control selectpicker" data-style="btn-secondary" data-live-search="true" required>
                                <option selected disabled>Не выбрано</option>
                                @if(isset($installers))
                                    @foreach($installers as $in)
                                        <option value="{{ $in->id }}">{{ $in->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="edit_installer_2">Монтажник 2:</label>
                            <select name="edit_installer_2" id="edit_installer_2" class="form-control selectpicker" data-style="btn-secondary" data-live-search="true" required>
                                <option selected disabled>Не выбрано</option>
                                @if(isset($installers))
                                    @foreach($installers as $in)
                                        <option value="{{ $in->id }}">{{ $in->name }}</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer border-top">
                        <button type="submit" class="btn btn-primary" disabled>Отправить</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Отмена</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @if(\Session::has('message'))
        <input type="hidden" id="message_input" value="{!! \Session::get('message') !!}">
    @endif
@endsection

@section('page-scripts')
    <script src="https://api-maps.yandex.ru/2.1/?apikey=74e8af9b-ed77-46cb-bc38-71da808481bd&lang=ru_RU" type="text/javascript"></script>
    <script type="text/javascript" src="{{ asset('js/routelist.js?v=2.1') }}"></script>
    <script type="text/javascript" src="{{ asset('js/routelist.map.js') }}"></script>
    <script type="text/javascript" src="{{ asset('js/doubleScroll.js') }}"></script>
    <script type="text/javascript">
        function validateForm() {
            let checkboxes = $('input[name="specialization[]"]:checked');
            let installer1 = $('#installer_1').val();
            let installer2 = $('#installer_2').val();

            if (checkboxes.length === 0) {
                alert('Выберите специализацию!');
                event.preventDefault();
                return false;
            } else if(!installer1 || !installer2) {
                alert('Выберите монтажника.');
                event.preventDefault();
            }
        }

        $(document).ready(function(){
            $('.routes-table').doubleScroll();
            if($("#message_input").val()){
                let msg = $("#message_input").val();
                $.growl.notice({
                    title: "Сообщение",
                    message: msg
                });
            }

            $(document).ready(function() {
                $(".toggleButton").click(function() {
                    var rowToToggle = $(this).closest("tr").next(".fromToggle");
                    rowToToggle.toggle();
                    $(this).closest("tr").hide();
                });

                $(".toggleFromButton").click(function() {
                    var rowToToggle = $(this).closest(".fromToggle").prevAll(".rowToToggle:first");
                    rowToToggle.toggle();
                    $(this).closest(".fromToggle").hide();
                });

            });
        });
        function sortTable(n) {
          var table, rows, switching, i, x, y, shouldSwitch, dir, switchcount = 0;
          table = document.getElementById("route_requests_table");
          switching = true;
          // Set the sorting direction to ascending:
          dir = "asc";
          /* Make a loop that will continue until
          no switching has been done: */
          while (switching) {
            // Start by saying: no switching is done:
            switching = false;
            rows = table.rows;
            /* Loop through all table rows (except the
            first, which contains table headers): */
            for (i = 1; i < (rows.length - 1); i++) {
              // Start by saying there should be no switching:
              shouldSwitch = false;
              /* Get the two elements you want to compare,
              one from current row and one from the next: */
              x = rows[i].getElementsByTagName("TD")[n];
              y = rows[i + 1].getElementsByTagName("TD")[n];
              /* Check if the two rows should switch place,
              based on the direction, asc or desc: */
              if (dir == "asc") {
                if (x.innerHTML.toLowerCase() > y.innerHTML.toLowerCase()) {
                  // If so, mark as a switch and break the loop:
                  shouldSwitch = true;
                  break;
                }
              } else if (dir == "desc") {
                if (x.innerHTML.toLowerCase() < y.innerHTML.toLowerCase()) {
                  // If so, mark as a switch and break the loop:
                  shouldSwitch = true;
                  break;
                }
              }
            }
            if (shouldSwitch) {
              /* If a switch has been marked, make the switch
              and mark that a switch has been done: */
              rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
              switching = true;
              // Each time a switch is done, increase this count by 1:
              switchcount ++;
            } else {
              /* If no switching has been done AND the direction is "asc",
              set the direction to "desc" and run the while loop again. */
              if (switchcount == 0 && dir == "asc") {
                dir = "desc";
                switching = true;
              }
            }
          }
        }
    </script>
@endsection
