<?php

use App\Http\Controllers\ArticleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PressingController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ServiceController;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/register', function () {
    return view('create');
})->name('create');

Route::middleware(['web', 'auth'])->get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

Route::middleware(['web', 'auth'])->get('/dashboard/manager/gestionnaire', function () {
    return view('manager.gestionnaire');
})->name('manager.gestionnaire');


Route::get('/dasboard/order', function () {
    return view('add-oder');
})->name('order');


Route::post('create/store',[PressingController::class, 'store'])->name('create.store');





Route::middleware(['auth'])->group(function () {

    /**
     * les routes de la gestion cleint
     */

    // Route pour afficher la liste des clients (avec filtres)
    Route::get('/dashboard/custumer', [ClientController::class, 'index'])->name('clients.index');
    // Routes pour les opérations CRUD (création, modification, suppression)
    Route::post('/clients', [ClientController::class, 'store'])->name('clients.store');
    Route::put('/clients/{client}', [ClientController::class, 'update'])->name('clients.update');
    Route::delete('/clients/{client}', [ClientController::class, 'destroy'])->name('clients.destroy');
    // Route pour l'historique des commandes
    Route::get('/clients/{client}/orders', [ClientController::class, 'getOrdersHistory'])->name('clients.orders');

    /**
     * les routes de la gestion des article 
     */

    Route::get('/articles', [ArticleController::class, 'index'])->name('articles.index');
    Route::post('/articles', [ArticleController::class, 'store'])->name('articles.store');
    Route::put('/articles/{article}', [ArticleController::class, 'update'])->name('articles.update');
    Route::delete('/articles/{article}', [ArticleController::class, 'destroy'])->name('articles.destroy');

    /**
     * les routes de la gestion des services 
     */

    Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
    Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
    Route::put('/services/{service}', [ServiceController::class, 'update'])->name('services.update');
    Route::delete('/services/{service}', [ServiceController::class, 'destroy'])->name('services.destroy');

});