ymaps.ready(init);

var myMap;

function init() {
    var latitude = document.getElementById('latitude').value;
    var longitude = document.getElementById('longitude').value;
    myMap = new ymaps.Map('map-complete',
        {
            center: [latitude, longitude],
            zoom: 17
        }, {
            searchControlProvider: 'yandex#search'
        }
    );
    myGeoObject = new ymaps.GeoObject({
            // Описание геометрии.
            geometry: {
                type: "Point",
                coordinates: [latitude, longitude]
            },
            // Свойства.
            properties: {
                // Контент метки.
                iconContent: 'Дом заявки',
                hintContent: 'Отцентруйте маркер, если данные не корректны'
            }
        }, {
            // Опции.
            // Иконка метки будет растягиваться под размер ее содержимого.
            preset: 'islands#blackStretchyIcon'
        }),
    myMap.geoObjects.add(myGeoObject)
}

function getCenter() {
    let coordinates = myMap.getCenter();
    if(coordinates !== undefined && coordinates != '')
    {
        document.getElementById('coordinates').value = coordinates; 
    }
    else
    {
        return false;
    }
}

function enableButton() {
    document.getElementById('submit-btn').disabled = false;
}

$("#success-alert").hide();
function showAlert() 
{
    $("#success-alert").show();
    window.setTimeout(function() 
    { 
        $("#success-alert").alert('close'); 
    }, 3000);               
};  