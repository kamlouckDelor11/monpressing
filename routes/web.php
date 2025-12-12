<?php

use App\Http\Controllers\ArticleController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PressingController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ManagerController;
use App\Http\Controllers\PayrollController;
use App\Http\Controllers\SpensController;

Route::get('/', function () {
    return view('welcome');
})->name('welcome');

Route::get('/register', function () {
    return view('create');
})->name('create');

Route::middleware(['web', 'auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
});






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


   // Routes pour la gestion des dépôts
    Route::get('/orders', [OrderController::class, 'index'])->name('order');
    Route::get('/manager_oder', [OrderController::class, 'manager_order'])->name('manager.order');
    Route::post('/orders', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/api/clients/search', [OrderController::class, 'searchClientByPhone']);
    Route::get('/api/articles/search', [OrderController::class, 'searchArticleByName']);
    Route::get('/api/services', [OrderController::class, 'getServices']);
    Route::get('/orders/filter', [OrderController::class, 'filterOrder'])->name('orders.filter');
    Route::get('/orders/{token}/details', [OrderController::class, 'showDetails'])->name('orders.details');
    Route::get('/orders/{token}/print', [OrderController::class, 'generateCouponPdf'])->name('orders.print');
    
    // Gestion des références pour les selects (in changée)
    Route::get('orders/references', [OrderController::class, 'getOrderReferences']);

    // Mise à jour du statut (utilisation du {token})
    Route::post('orders/{token}/status', [OrderController::class, 'updateDeliveryStatus']);

    // Encaissement (utilisation du {token})
    Route::post('orders/{token}/cash-in', [OrderController::class, 'cashIn']);


    //routes autres depens

    Route::prefix('spenses')->name('spenses.')->group(function () {
        // Vue principale de l'interface des dépenses
        Route::get('/', [SpensController::class, 'index'])->name('index');

        // API pour les catégories de dépenses (CRUD)
        Route::get('categories/data', [SpensController::class, 'getCategoriesData'])->name('categories.data');
        Route::post('categories/store', [SpensController::class, 'storeCategory'])->name('categories.store');
        // Note: Laravel utilise 'PUT' pour update, mais le formulaire utilisera POST avec _method=PUT
        Route::put('categories/{spens}', [SpensController::class, 'updateCategory'])->name('categories.update');

        // API pour la comptabilisation du panier
        Route::post('comptabiliser', [SpensController::class, 'comptabiliserDepense'])->name('comptabiliser');

        // API pour l'historique des transactions
        Route::get('transactions/history/{spens}', [SpensController::class, 'getTransactionsHistory'])->name('transactions.history');
        
        // API pour l'annulation (Validation/Annulation)
        Route::post('transactions/cancel', [SpensController::class, 'cancelTransaction'])->name('cancel.item');
    });


});


// Assurez-vous que l'admin est connecté et a le bon rôle
Route::middleware(['auth', 'admin'])->prefix('manager')->group(function () {
    
    // Page de gestion (GET)
    Route::get('/gestionnaire', [ManagerController::class, 'index'])->name('manager.gestionnaire');
    
    // API/AJAX pour la liste des utilisateurs (gère la pagination)
    Route::get('/users', [ManagerController::class, 'getUsers'])->name('manager.users.index');
    
    // Création d'un nouvel utilisateur (POST)
    Route::post('/user/store', [ManagerController::class, 'storeUser'])->name('manager.user.store');
    
    // Modification d'un utilisateur (PUT/PATCH)
    Route::post('/user/update/{user:token}', [ManagerController::class, 'updateUser'])->name('manager.user.update');


    Route::prefix('payroll')->group(function () {
        
        // Vue principale (État du personnel, Paie et Paiement)
        Route::get('/', [PayrollController::class, 'index'])->name('manager.payroll.index');
        
        // Gestion des Employés
        Route::get('/employe/{token}', [PayrollController::class, 'getEmploye']);
        Route::post('/employe', [PayrollController::class, 'storeEmploye'])->name('manager.employe.store');
        Route::put('/employe/{token}', [PayrollController::class, 'updateEmploye'])->name('manager.employe.update');
        
        // Gestion de la Paie (Création de l'en-tête et des items)
        Route::get('/data', [PayrollController::class, 'getPayrollData'])->name('manager.payroll.data');
        Route::post('/paie', [PayrollController::class, 'storePaie'])->name('manager.paie.store');
        
        // Paiement de la Paie
        Route::get('/unpaid-paie/{paie}', [PayrollController::class, 'getUnpaidPaie']);
        Route::post('/pay', [PayrollController::class, 'payPaie'])->name('manager.paie.pay');
        
        // Bulletin de Paie
       // 1. Route pour afficher la page de sélection du mois/année
        Route::get('/select-bulletin/{employe}', [PayrollController::class, 'selectBulletin'])
            ->name('manager.bulletin.select');

        // 2. Route pour la génération finale du PDF (celle que vous aviez déjà)
            // Route pour la génération finale du PDF
        Route::get('/generate-bulletin/{employe}', [PayrollController::class, 'generateBulletin'])
            ->name('manager.bulletin.generate');

        // Route pour récupérer l'aperçu HTML du bulletin via AJAX
        Route::get('/preview-bulletin/{employe}', [PayrollController::class, 'previewBulletin'])
            ->name('manager.bulletin.preview');


    });

});


