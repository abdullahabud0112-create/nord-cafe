<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\CashierController;

// Customer Self-Order Routes (matching Nordic Brew System design flow)
Route::get('/', [CustomerController::class, 'catalog'])->name('customer.catalog');
Route::redirect('/catalog', '/');
Route::get('/product/{id}', [CustomerController::class, 'productDetail'])->name('customer.productDetail');
Route::get('/cart', [CustomerController::class, 'cart'])->name('customer.cart');
Route::get('/checkout', [CustomerController::class, 'checkout'])->name('customer.checkout');
Route::post('/order/place', [CustomerController::class, 'placeOrder'])->name('customer.placeOrder');
Route::get('/ticket/{orderNumber}', [CustomerController::class, 'ticket'])->name('customer.ticket');
Route::get('/track/{orderNumber?}', [CustomerController::class, 'trackStatus'])->name('customer.trackStatus');
Route::get('/api/order-status/{orderNumber}', [CustomerController::class, 'getOrderStatusApi'])->name('customer.statusApi');

// Cashier POS Routes (matching Nordic Brew System cashier design flow)
Route::prefix('cashier')->middleware('role')->group(function () {
    Route::get('/', [CashierController::class, 'incomingOrders'])->name('cashier.incomingOrders');
    Route::get('/scan', [CashierController::class, 'scanBarcode'])->name('cashier.scanBarcode');
    Route::post('/search-barcode', [CashierController::class, 'searchBarcode'])->name('cashier.searchBarcode');
    Route::get('/order/{id}', [CashierController::class, 'orderDetail'])->name('cashier.orderDetail');
    Route::get('/order/{id}/update-status', [CashierController::class, 'editStatus'])->name('cashier.editStatus');
    Route::post('/order/{id}/update-status', [CashierController::class, 'updateStatus'])->name('cashier.updateStatus');
    Route::post('/order/{id}/confirm-payment', [CashierController::class, 'confirmPayment'])->name('cashier.confirmPayment');
    Route::post('/clear-completed', [CashierController::class, 'clearCompleted'])->name('cashier.clearCompleted');
    Route::delete('/order/{id}', [CashierController::class, 'deleteOrder'])->name('cashier.deleteOrder');

    // Menu Management
    Route::get('/menu', [CashierController::class, 'menuManager'])->name('cashier.menuManager');
    Route::post('/menu/{id}/toggle-availability', [CashierController::class, 'toggleAvailability'])->name('cashier.toggleAvailability');
    Route::post('/menu/{id}/update', [CashierController::class, 'updateProduct'])->name('cashier.updateProduct');
    Route::post('/menu/store', [CashierController::class, 'storeProduct'])->name('cashier.storeProduct');
    Route::delete('/menu/{id}', [CashierController::class, 'deleteProduct'])->name('cashier.deleteProduct');
});
