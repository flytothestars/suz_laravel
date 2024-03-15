var myMap;
// Функция ymaps.ready() будет вызвана, когда
// загрузятся все компоненты API, а также когда будет готово DOM-дерево.
ymaps.ready(init);
function init(){
    let table = document.getElementById("route_requests_table");
    let basic_coordinates = table.rows[1].dataset.basicCoordinates;
    let map_center = JSON.parse(basic_coordinates);
    // Создаем карту с добавленными на нее кнопками.
    myMap = new ymaps.Map('map', {
        center: map_center,
        zoom: 13,
        controls: []
    }, {
        buttonMaxWidth: 300
    });
    addAllObjects();
}

/**
 * Добавляет координаты всех заявок на карту.
 */
function addAllObjects(){
    let table = document.getElementById("route_requests_table");
    let objects = [];
    for(let i = 1, row; row = table.rows[i]; i++){
        let point = JSON.parse(row.dataset.coordinates);
        let placemark = new ymaps.Placemark(point,
            {
                requestId: row.dataset.requestId,
                iconContent: "№ " + row.dataset.requestId,
            },
            {
                preset: 'islands#redStretchyIcon'
            }
        );
        placemark.events.add(['click'], function(e){
            let id = e.originalEvent.target.properties._data.requestId;
            let element = document.body.querySelector('.request-row[data-request-id="' + id + '"]');
            element.style.cssText = "background-color: #eee;";
            setTimeout(function(){
                element.style.cssText = "background-color: initial;";
            }, 100);
        });
        objects.push(placemark);
    }
    for(let i = 0; i < objects.length; i++){
        myMap.geoObjects.add(objects[i]);
    }
}

/**
 * Показывает маршрут на карте при нажатии на бригаду.
 */
var selected_route = 0;
function selectRoute(id){
    if(selected_route !== id){
        let points = [];
        $.ajax({
            url: '/select_route',
            data: {
                id: id
            },
            async: false,
            dataType: 'json',
            success: function(response){
                points = response;
            }
        });
        /**
         * Создаем мультимаршрут.
         * Первым аргументом передаем модель либо объект описания модели.
         * Вторым аргументом передаем опции отображения мультимаршрута.
         * @see https://api.yandex.ru/maps/doc/jsapi/2.1/ref/reference/multiRouter.MultiRoute.xml
         * @see https://api.yandex.ru/maps/doc/jsapi/2.1/ref/reference/multiRouter.MultiRouteModel.xml
         */
        let multiRoute = new ymaps.multiRouter.MultiRoute({
            // Описание опорных точек мультимаршрута.
            referencePoints: points,
            // Параметры маршрутизации.
            params: {
                // Ограничение на максимальное количество маршрутов, возвращаемое маршрутизатором.
                results: 2
            }
        }, {
            // Автоматически устанавливать границы карты так, чтобы маршрут был виден целиком.
            boundsAutoApply: true
        });
        // Удаляем все метки из карты.
        myMap.geoObjects.removeAll();
        // Добавляем мультимаршрут на карту.
        myMap.geoObjects.add(multiRoute);
        selected_route = id;
    }
    else{
        // Удаляем все метки из карты.
        myMap.geoObjects.removeAll();
        addAllObjects();
        selected_route = 0;
    }
}

/**
 * Перемещает центр карты на координаты выбранной заявки.
 */
function selectRequest(e){
    let coordinates = JSON.parse(e.dataset.coordinates);
    myMap.setCenter(coordinates, 15, { duration: 500 });
}