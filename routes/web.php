<?php

use App\Http\Controllers\CashierController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\KitchenController;
use Illuminate\Support\Facades\Route;

// Customer Self-Order Routes (matching Nordic Brew System design flow)
Route::get('/', [CustomerController::class, 'catalog'])->name('customer.catalog');

// Health check for deployment smoke tests (Vercel container guide)
Route::get('/health', static function (): \Illuminate\Http\JsonResponse {
    return response()->json([
        'status' => 'ok',
        'framework' => 'Laravel',
        'timestamp' => now()->toIso8601String(),
    ]);
});
Route::redirect('/catalog', '/');
Route::get('/product/{id}', [CustomerController::class, 'productDetail'])->name('customer.productDetail');
Route::get('/cart', [CustomerController::class, 'cart'])->name('customer.cart');
Route::get('/checkout', [CustomerController::class, 'checkout'])->name('customer.checkout');
// Split order confirmation into 2 URLs: /checkout/minuman (step 1) → /checkout/makanan (step 2)
Route::get('/checkout/minuman', [CustomerController::class, 'checkoutDrink'])->name('customer.checkoutDrink');
Route::get('/checkout/makanan', [CustomerController::class, 'checkoutFood'])->name('customer.checkoutFood');
Route::post('/order/place', [CustomerController::class, 'placeOrder'])->name('customer.placeOrder');
Route::get('/ticket/{orderNumber}', [CustomerController::class, 'ticket'])->name('customer.ticket');
Route::get('/track/{orderNumber?}', [CustomerController::class, 'trackStatus'])->name('customer.trackStatus');
Route::get('/api/order-status/{orderNumber}', [CustomerController::class, 'getOrderStatusApi'])->name('customer.statusApi');

// Cashier POS Routes (matching Nordic Brew System cashier design flow)
// No middleware: direct access for kiosk mode (no login system)
Route::prefix('cashier')->group(function () {
    Route::get('/', [CashierController::class, 'incomingOrders'])->name('cashier.incomingOrders');
    Route::get('/scan', [CashierController::class, 'scanBarcode'])->name('cashier.scanBarcode');
    Route::post('/search-barcode', [CashierController::class, 'searchBarcode'])->name('cashier.searchBarcode');
    Route::get('/feed', [CashierController::class, 'feed'])->name('cashier.feed');
    Route::get('/order/{id}', [CashierController::class, 'orderDetail'])->name('cashier.orderDetail');
    Route::get('/order/{id}/update-status', [CashierController::class, 'editStatus'])->name('cashier.editStatus');
    Route::post('/order/{id}/update-status', [CashierController::class, 'updateStatus'])->name('cashier.updateStatus');
    // Department-scoped processing: Cashier may only touch drink items.
    Route::post('/order/{id}/department-status', [CashierController::class, 'updateDepartmentStatus'])->name('cashier.updateDepartmentStatus');
    Route::post('/order/{id}/item/{itemId}/status', [CashierController::class, 'updateItemStatus'])->name('cashier.updateItemStatus');
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

// Kitchen / Dapur operational routes — food items only.
// No middleware: direct access for kiosk mode (no login system)
Route::prefix('kitchen')->group(function () {
    Route::get('/', [KitchenController::class, 'index'])->name('kitchen.index');
    Route::get('/feed', [KitchenController::class, 'feed'])->name('kitchen.feed');
    Route::get('/order/{id}', [KitchenController::class, 'show'])->name('kitchen.show');
    Route::post('/order/{id}/department-status', [KitchenController::class, 'updateStatus'])->name('kitchen.updateStatus');
    Route::post('/order/{id}/item/{itemId}/status', [KitchenController::class, 'updateItemStatus'])->name('kitchen.updateItemStatus');
});
