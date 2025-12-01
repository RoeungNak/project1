<?php

use App\Http\Controllers\admin\AuthController;
use App\Http\Controllers\admin\BrandController;
use App\Http\Controllers\admin\CategoryController;
use App\Http\Controllers\Admin\FinanceController;
use App\Http\Controllers\admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\SaleReportController;
use App\Http\Controllers\admin\SizeController;
use App\Http\Controllers\admin\SupplierController;
use App\Http\Controllers\admin\TempImageController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\front\AccountController;
use App\Http\Controllers\front\OrderController;
use App\Http\Controllers\Front\PaymentController;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\front\ProductController as FrontProductController;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use Telegram\Bot\Laravel\Facades\Telegram;


Route::post('/admin/login', [AuthController::class, 'authenticate']);
Route::get('/latest-products', [FrontProductController::class, 'latestProducts']);
Route::get('/get-categories', [FrontProductController::class, 'getCategories']);
Route::get('/get-brands', [FrontProductController::class, 'getBrands']);
Route::get('/get-products', [FrontProductController::class, 'getProducts']);
Route::get('/get-product/{id}', [FrontProductController::class, 'getProduct']);
Route::post('/register', [AccountController::class, 'register']);
Route::post('/login', [AccountController::class, 'authenticate']);
Route::post('/upload-payment', [PaymentController::class, 'uploadPayment']);

Route::group(['middleware' => ['auth:sanctum', 'checkUserRole']], function () {
    Route::post('/save-order', [OrderController::class, 'saveOrder']);
    Route::get('/order/{id}', [OrderController::class, 'getOrder']);
    Route::get('/get-orders', [AccountController::class, 'getOrders']);
    Route::get('/user/order/{id}', [AccountController::class, 'getOrderDetails']);
});
// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');
Route::group(['middleware' => ['auth:sanctum', 'checkAdminRole']], function () {
    Route::resource('categoies', CategoryController::class);
    Route::resource('brands', BrandController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('sizes', SizeController::class);
    Route::resource('products', ProductController::class);
    Route::post('/temp-images', [TempImageController::class, 'store']);
    Route::delete('/temp-images/{id}', [TempImageController::class, 'destroy']);
    Route::post('/save-product-image', [ProductController::class, 'saveProductImage']);
    Route::post('/change-product-default-image', [ProductController::class, 'updateDefaultImage']);
    Route::delete('/product-delete-image/{id}', [ProductController::class, 'destroyImage']);
    Route::get('/orders', [AdminOrderController::class, 'index']);
    Route::get('/orders/{id}', [AdminOrderController::class, 'details']);
    Route::put('/orders/{id}', [AdminOrderController::class, 'update']);
    Route::get('users/{id}', [UserController::class, 'show']);
    Route::put('users/{id}', [UserController::class, 'update']);
    Route::delete('users/{id}', [UserController::class, 'destroy']);
    Route::get('users', [UserController::class, 'index']);
    Route::get('users/counts', [UserController::class, 'counts']);
    // Finance summary
    Route::get('/finance', [FinanceController::class, 'getFinanceSummary']);
    Route::get('/sales-report', [SaleReportController::class, 'index']);
    Route::get('/completed-sales', [AdminOrderController::class, 'completedSales']);
});

Route::post('/send-telegram', function (Request $request) {
    $chat_id = env('TELEGRAM_CHAT_ID');
    $bot_token = env('TELEGRAM_BOT_TOKEN');

    $message = $request->input('message');
    $image = $request->file('image');

    if ($image) {
        // Send photo with caption
        $response = Http::attach(
            'photo',
            file_get_contents($image->getRealPath()),
            $image->getClientOriginalName()
        )->post("https://api.telegram.org/bot{$bot_token}/sendPhoto", [
            'chat_id' => $chat_id,
            'caption' => $message,
        ]);
    } else {
        // Send only text
        $response = Http::post("https://api.telegram.org/bot{$bot_token}/sendMessage", [
            'chat_id' => $chat_id,
            'text' => $message,
        ]);
    }

    return $response->json();
});
