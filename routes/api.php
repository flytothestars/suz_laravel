<?php

use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\Catalogs\WorkTypeController;
use App\Http\Controllers\API\EgovQrController;
use App\Http\Controllers\API\InventoryController;
use App\Http\Controllers\API\KitController;
use App\Http\Controllers\API\MaterialController;
use App\Http\Controllers\API\RouteListController;
use App\Http\Controllers\API\SearchController;
use App\Http\Controllers\API\SuzRequestController;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\MaterialLimitController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OltController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::get('address', [EgovQrController::class, 'getAddress'])->name('api.egov-qr.address');

Route::group(['prefix' => 'material_limit'], function () {
    Route::group(['prefix' => 'statistic'], function () {
        Route::post('store', [MaterialLimitController::class, 'storeStatistic'])->name('material_limit.stat.store');
    });
});

Route::prefix('auth')->group(function () {
    Route::post('/login', [AuthController::class, 'login'])->name('api-login');
    Route::patch('/reset', [AuthController::class, 'reset'])->name('api-reset');
});

Route::middleware(['auth_api'])->group(function () {

    Route::post('/getGpon/{id_house}', [OltController::class, 'getLogin']);

    Route::prefix('catalog')->group(function () {
        Route::prefix('workType')->group(function () {
            Route::get('/', [WorkTypeController::class, 'index'])->name('api-catalog.workType.index');
            Route::get('/group', [WorkTypeController::class, 'group'])->name('api-catalog.workType.group');
        });
    });

    Route::prefix('user')->group(function () {
        Route::get('profile', [UserController::class, 'profile']);
        Route::get('index', [UserController::class, 'list']);
    });

    Route::get('/requests/show/{id}', [SuzRequestController::class, 'show'])->name('api-requests.show');

    Route::middleware(['role:техник'])->group(function () {
        Route::get('/routelist', [RouteListController::class, 'indexInstaller'])->name('api.routelist.index');

        Route::prefix('inventory')->group(function () {
            Route::get('/{type}', [InventoryController::class, 'myInventory'])->name('api.inventory.myInventory');

            Route::prefix('kit')->group(function () {
                Route::post('/return', [InventoryController::class, 'returnKit'])->name('api.kit.return');
            });

            Route::prefix('materials')->group(function () {
                Route::post('/return', [InventoryController::class, 'returnMaterial'])->name('api.materials.return');
            });
        });
    });

    Route::middleware(['role:администратор|техник'])->group(function () {
        Route::prefix('requests')->group(function () {
            Route::post('complete', [SuzRequestController::class, 'complete'])->name('api-requests.complete');
            Route::prefix('inventory')->group(function () {
                Route::get('getKitInfo', [KitController::class, 'getInstallerKits']);
                Route::get('getMaterialInfo', [MaterialController::class, 'getInstallerMaterials']);
            });
            Route::get('search', [SearchController::class, 'search'])->name('api-requests.search');
        });

        Route::prefix('kit')->group(function () {
            Route::get('/search', [InventoryController::class, 'searchKit'])->name('api.kit.search');
        });

        Route::prefix('egov-qr')->group(function () {
            Route::get('generate', [EgovQrController::class, 'generate'])->name('api.egov-qr.generate');
            Route::get('check', [EgovQrController::class, 'check'])->name('api.egov-qr.check');
            Route::post('offline-sign', [EgovQrController::class, 'offlineSign'])->name('api.egov-qr.offline-sign');
        });
    });

    Route::middleware(['role:администратор|кладовщик'])->group(function () {
        Route::prefix('inventory')->group(function () {
            Route::prefix('kit')->group(function () {
                Route::post('/acceptStockRequest', [InventoryController::class, 'kitRequestGet'])->name('api.inventory.kit.stock.accept');
                Route::post('/rejectStockRequest', [InventoryController::class, 'kitRequestDelete'])->name('api.inventory.kit.stock.reject');
            });
            Route::prefix('materials')->group(function () {
                Route::post('/acceptStockRequest', [InventoryController::class, 'materialRequestGet'])->name('api.inventory.materials.stock.accept');
                Route::post('/rejectStockRequest', [InventoryController::class, 'materialRequestDelete'])->name('api.inventory.materials.stock.reject');
            });
        });
    });


    Route::middleware(['role:администратор|диспетчер'])->group(function () {
        Route::prefix('requests')->group(function () {
            Route::post('assign', [SuzRequestController::class, 'assign'])->name('api-requests.assign');
            Route::post('cancel', [SuzRequestController::class, 'cancel'])->name('api-requests.cancel');
            Route::post('postpone', [SuzRequestController::class, 'postpone'])->name('api-requests.postpone');
            Route::post('return', [SuzRequestController::class, 'return'])->name('api-requests.return');
            Route::post('imageUpload', [SuzRequestController::class, 'ajaxImageUploadPost'])->name('api-requests.ajaxUpload');
            Route::get('story/{id}', [SuzRequestController::class, 'story'])->name('api-requests.story');
        });

        Route::prefix('routeList')->group(function () {
            Route::post('/create', [RouteListController::class, 'store'])->name('api-routeList.store');
        });
    });

});


