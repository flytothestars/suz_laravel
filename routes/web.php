<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*
|--------------------------------------------------------------------------
| Here we go!
|--------------------------------------------------------------------------
|
*/

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\OltController;
use App\Http\Controllers\SearchController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Auth::routes([
    'verify' => false,
    'register' => false
]);

Route::get('/', [HomeController::class, 'index'])->name('home');


Route::get('api', 'AdminController@api');

Route::get('/logout', '\App\Http\Controllers\Auth\LoginController@logout');

/*
|--------------------------------------------------------------------------
| SOAP
|--------------------------------------------------------------------------
*/
Route::prefix('soapclient')->group(function () {
    Route::get('show_functions', 'SoapClientController@showFunctions');
    Route::get('run/{name}', 'SoapClientController@runFunction');
    Route::get('getEquipmentKitsType', 'SoapClientController@getEquipmentKitsType');
    Route::get('getServiceAddress', 'SoapClientController@getServiceAddress');
});

Route::middleware(['auth', 'role:администратор|диспетчер|инспектор'])->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Список заявок
    |--------------------------------------------------------------------------
    */
    Route::get('/requests', [
        'as' => 'requests',
        'uses' => 'SuzRequestController@index'
    ]);
});

Route::middleware(['auth', 'role:администратор|просмотр заявок'])->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Список заявок
    |--------------------------------------------------------------------------
    */
    Route::get('/storyByGroup', [
        'as' => 'storyByGroup',
        'uses' => 'SuzRequestController@storyByGroup'
    ]);
});

Route::middleware(['auth', 'role:администратор|диспетчер|супервизер'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Страница маршрутизации заявок
    |--------------------------------------------------------------------------
    */
    Route::get('/routing', [
        'as' => 'routing',
        'uses' => 'RouteListController@index'
    ]);

    /*
    |--------------------------------------------------------------------------
    | Страница назначения заявки бригадиру
    |--------------------------------------------------------------------------
    */
    Route::get('/requests/{id}/assign', [
        'uses' => 'SuzRequestController@assignIndex'
    ]);

    /*
    |--------------------------------------------------------------------------
    | Назначение заявки
    |--------------------------------------------------------------------------
    */
    Route::post('/request.assign', [
        'uses' => 'SuzRequestController@assign',
        'as' => 'request.assign'
    ]);


    /*
    |--------------------------------------------------------------------------
    | Страница отложения заявки
    |--------------------------------------------------------------------------
    */
    Route::get('/requests/{id}/postpone', [
        'uses' => 'SuzRequestController@postponeIndex'
    ]);

    /*
    |--------------------------------------------------------------------------
    | Маршрут формы отложения заявки
    |--------------------------------------------------------------------------
    */
    Route::post('/request.postpone', [
        'uses' => 'SuzRequestController@postpone',
        'as' => 'request.postpone'
    ]);

    /*
    |--------------------------------------------------------------------------
    | Страница отмены заявки
    |--------------------------------------------------------------------------
    */
    Route::get('/requests/{id}/cancel', [
        'uses' => 'SuzRequestController@cancelIndex'
    ]);

    /*
    |--------------------------------------------------------------------------
    | Маршрут отмены заявки
    |--------------------------------------------------------------------------
    */
    Route::post('/request.cancel', [
        'uses' => 'SuzRequestController@cancel',
        'as' => 'request.cancel'
    ]);
});

Route::middleware(['auth', 'role:администратор|диспетчер|супервизер'])->group(function () {

    Route::get('/routelists/{id}', 'RouteListController@show');
});

Route::middleware(['auth', 'role:администратор|диспетчер|просмотр маршрута|супервизер'])->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Страница маршрутизации заявок
    |--------------------------------------------------------------------------
    */
    Route::get('/routing', [
        'as' => 'routing',
        'uses' => 'RouteListController@index'
    ]);
});

Route::middleware(['auth', 'role:администратор|кладовщик|супервизер|диспетчер'])->group(function () {
    Route::get('/users', [
        'uses' => 'UserController@index',
        'as' => 'users.index'
    ]);
    Route::get('/users/{id}', [
        'uses' => 'UserController@show'
    ]);
});

Route::middleware(['auth', 'ajax'])->group(function () {
    /*
    |--------------------------------------------------------------------------
    | Удаление маршрутного листа
    |--------------------------------------------------------------------------
    */
    Route::post('/routelist.destroy', [
        'uses' => 'RouteListController@destroy',
        'as' => 'routelist.destroy'
    ]);

    Route::post('/admin.ajax.soap_settings_change', [
        'as' => 'admin.ajax.soap_settings_change',
        'uses' => 'AdminController@changeSoapSetting'
    ]);

    Route::get('/select_route', [
        'uses' => 'RouteListController@getRouteCoordinates'
    ]);

    Route::get('/get_routelist_installers_as_options', [
        'uses' => 'RouteListController@getRouteListInstallers'
    ]);

    Route::get('/get_location_by_department', 'CatalogController@getLocationByDepartment');

    Route::get('/getEquipmentList', 'EquipmentController@getEquipmentList');

    Route::get('/getEquipmentKit', 'EquipmentController@getKit');

    Route::get('/getEquipmentsReturnModal', 'EquipmentController@getEquipmentsReturnModal');

    Route::get('/getInstallerEquipment', 'EquipmentController@getInstallerEquipment');

    Route::get('/getLocationsOptionsByDepartment', 'CatalogController@getLocationsOptionsByDepartment');

    Route::get('/getParentStocksOptionsByDepartment', 'CatalogController@getParentStocksOptionsByDepartment');

    Route::get('/getInstallerKits', 'KitController@getInstallerKits');

    Route::get('/getInstallerMaterials', 'MaterialController@getInstallerMaterials');

    Route::get('/getKitsEquipment', 'KitController@getKitsEquipment');

    Route::get('/getKitsReturnModal', 'KitController@getKitsReturnModal');

    Route::get('/getMaterialsReturnModal', 'MaterialController@getMaterialsReturnModal');

    Route::get('/getStockholdersByLocation', 'StockController@getStockholdersByLocation');

    Route::get('/search_users', [SearchController::class, 'searchUser']);
});

Route::middleware(['auth', 'role:администратор|кладовщик'])->group(function () {
    // инвентарь на складе
    Route::get('/inventory', 'InventoryController@index')->name('inventory.index');
    Route::get('/inventory/movement-story', 'InventoryController@movementStory')->name('inventory.movement_story');

    Route::get('/materials', 'MaterialController@index')->name('materials.index');
    Route::get('/materials/types', 'MaterialController@types')->name('materials.types');
    Route::get('/materials/create', 'MaterialController@create');
    Route::get('/materials/types/delete', 'MaterialController@deleteType');
    Route::get('/materials/upload', 'MaterialController@uploadIndex');

    Route::post('/materials/types/store', 'MaterialController@storeType');
    Route::post('/materials/store', 'MaterialController@store');
    Route::post('/materials/delete', 'MaterialController@destroy');
    Route::post('/materials/upload', 'MaterialController@upload');
    Route::post('/materials/edit_limit', 'MaterialController@editLimit');
});

Route::middleware(['auth', 'role:техник'])->group(function () {
    // Мой маршрутный лист
    Route::get('/routelist', 'RouteListController@indexInstaller')->name('routelist.index');
    // Мой инвентарь
    Route::get('/my_inventory', 'InventoryController@myInventory')->name('inventory.myInventory');
    // Поиск комплекта
    Route::post('/my_inventory/search', [SearchController::class, 'searchKit'])->name('searchKit');
    // Регистрация в Телеграм
    Route::get('/telegram', 'UserController@checkTelegramAuthorization');
});

/*
|--------------------------------------------------------------------------
| Панель администратора
|--------------------------------------------------------------------------
*/
Route::group(['prefix' => 'admin', 'middleware' => 'auth'], function () {
    Route::get('/', 'AdminController@index')->name('admin.index');
});

Route::post('/validate-captcha', [LoginController::class, 'validateCaptcha'])->name('validate-captcha');

Route::middleware(['auth'])->group(function () {
    Route::get('/requests/{id}/return', [
        'uses' => 'SuzRequestController@returnIndex'
    ]);
    Route::post('/requests/{id}/return', [
        'uses' => 'SuzRequestController@returnIndex'
    ]);
    Route::post('/requests/{id}/returnAction', [
        'uses' => 'SuzRequestController@return'
    ]);
    Route::post('/requests/{id}/complete', [
        'uses' => 'SuzRequestController@completeIndex'
    ]);
    Route::post('/requests/{id}/completeAction', [
        'uses' => 'SuzRequestController@complete'
    ]);
    Route::post('/requests/{id}/material', [
        'uses' => 'SuzRequestController@writeMaterial'
    ]);

    Route::get('/requests/{id}/story', [
        'uses' => 'SuzRequestController@story',
        'as' => 'request'
    ]);
    Route::get('/requests/{id}', [
        'uses' => 'SuzRequestController@show',
        'as' => 'request'
    ]);
    Route::post('/download_word', 'SuzRequestController@downloadWord');
    Route::post('ajaxImageUpload', 'AjaxImageUploadController@ajaxImageUploadPost')->name('ajaxImageUpload');
});

// -------------------

Route::post('/stocks/delete', [
    'uses' => 'StockController@delete',
    'middleware' => ['auth', 'ajax', 'role:администратор']
]);

Route::get('/get_mass_equipment', [
    'uses' => 'InventoryController@getMassEquipment',
    'middleware' => ['auth', 'ajax', 'role:администратор|кладовщик']
]);

Route::post('/fix_equipment', [
    'uses' => 'SuzRequestController@fixEquipment',
    'middleware' => ['auth', 'ajax', 'role:администратор']
]);

Route::middleware(['auth', 'stock.manager'])->group(function () {
    Route::get('/equipment', 'AdminController@equipment');
    Route::get('/search_equipment', 'AdminController@searchEquipment');
    Route::post('/delete_equipment', 'AdminController@deleteEquipment');
    Route::post('/change_equipment_owner', 'AdminController@changeEquipmentOwner');
    Route::post('/change_equipment_stock', 'AdminController@changeEquipmentStock');
});

Route::middleware(['auth', 'role:администратор'])->group(function () {
    Route::get('/stocks', 'StockController@index');
    Route::get('/stocks/create', 'StockController@create');
    Route::get('/stocks/{id}/edit', 'StockController@edit');
    Route::post('/stocks/{id}/update', 'StockController@update');
    Route::get('/soap_history', 'AdminController@soapHistory');
    Route::get('/settings', [HomeController::class, 'settings'])->name('settings');
    Route::get('/auth_by_id/{id}', [HomeController::class, 'authById'])->name('web-authById');
    Route::post('/stocks/store', [
        'uses' => 'StockController@store'
    ]);
});

Route::middleware(['auth', 'role:администратор|кладовщик|диспетчер'])->group(function () {
    Route::get('/report', [
        'uses' => 'ReportController@index'
    ]);
    Route::get('/requests-report/', 'ReportController@requestsIndex');
    Route::get('/downloadReport', 'ReportController@export');
    Route::get('/downloadRequests', 'ReportController@exportRequests');
});

Route::middleware('auth')->group(function () {
    Route::post('/kit_request_get', [
        'uses' => 'InventoryController@kitRequestGet',
        'as' => 'kit_request_get'
    ]);
    Route::post('/kit_request_delete', [
        'uses' => 'InventoryController@kitRequestDelete',
        'as' => 'kit_request_delete'
    ]);
    Route::post('/materials_request_get', [
        'uses' => 'InventoryController@materialsRequestGet',
        'as' => 'materials_request_get'
    ]);
    Route::post('/materials_request_delete', [
        'uses' => 'InventoryController@materialsRequestDelete',
        'as' => 'materials_request_delete'
    ]);
    Route::post('/kit_rollback', [
        'uses' => 'InventoryController@rollbackKit',
        'as' => 'kit_rollback'
    ]);
    Route::post('/issue_kit', [
        'uses' => 'InventoryController@issue',
        'as' => 'issue_kit'
    ]);

    Route::post('/issue_materials', [
        'uses' => 'InventoryController@issueMaterials',
        'as' => 'issue_materials'
    ]);

    Route::post('/move_kit', [
        'uses' => 'InventoryController@moveKit',
        'as' => 'move_kit'
    ]);

    Route::post('/move_materials', [
        'uses' => 'InventoryController@moveMaterials',
        'as' => 'move_materials'
    ]);

    Route::post('/move_equipment', [
        'uses' => 'InventoryController@moveEquipment',
        'as' => 'move_equipment'
    ]);

    Route::post('/return_equipment', [
        'uses' => 'InventoryController@returnEquipment',
        'as' => 'return_equipment'
    ]);

    Route::post('/return_kit', [
        'uses' => 'InventoryController@returnKit',
        'as' => 'return_kit'
    ]);

    Route::post('/return_material', [
        'uses' => 'InventoryController@returnMaterial',
        'as' => 'return_material'
    ]);
    Route::get('/search', [SearchController::class, 'search']);

    Route::get('roles', [
        'uses' => 'RoleController@index',
        'as' => 'roles'
    ]);
    Route::post('/routelist.store', [
        'uses' => 'RouteListController@store',
        'as' => 'routelist.store'
    ]);

    Route::post('/routelist.update', [
        'uses' => 'RouteListController@update',
        'as' => 'routelist.update'
    ]);

    Route::post('/user.update', [
        'uses' => 'UserController@update',
        'as' => 'user.update'
    ]);

    Route::get('/kit/{id}', 'KitController@show');
    Route::get('/equipment/{id}', 'EquipmentController@show');
    Route::get('/material/{id}', 'MaterialController@show');

    Route::get('/check-olts', [
        'uses' => 'OltController@checkOltsPage',
        'as' => 'check.olts'
    ]);
    Route::post('/check-olts', [
        'uses' => 'OltController@checkOlts',
        'as' => 'check.olts'
    ]);
});

Route::get('/reset', 'Auth\ResetPasswordController@index')->name('reset');
Route::post('/update_password', 'Auth\ResetPasswordController@update')->name('update_password');

Route::get('/get_rows', 'ReportController@getRows2');

Route::middleware(['auth', 'role:администратор|диспетчер|супервизер'])->group(function () {
    Route::get('/report-card', 'ReportController@reportCard');
});

Route::post('getJponLogin/{id_house}', [OltController::class, 'getLogin']);
