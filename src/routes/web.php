<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\LikeController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\RegisteredUserController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ReviewController;
use App\Http\Requests\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

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

Route::get('/',[ItemController::class, 'index'])->name('items.list');
Route::get('/item/{item}',[ItemController::class, 'detail'])->name('item.detail');
Route::get('/item', [ItemController::class, 'search']);
Route::post('/stripe/webhook', [PurchaseController::class, 'webhook']);

Route::middleware(['auth','verified'])->group(function () {
    Route::get('/sell',[ItemController::class, 'sellView']);
    Route::post('/sell',[ItemController::class, 'sellCreate']);
    Route::post('/item/like/{item_id}',[LikeController::class, 'create']);
    Route::post('/item/unlike/{item_id}',[LikeController::class, 'destroy']);
    Route::post('/item/comment/{item_id}',[CommentController::class, 'create']);
    Route::get('/purchase/{item_id}',[PurchaseController::class, 'index'])->middleware('purchase')->name('purchase.index');
    Route::post('/purchase/{item_id}', [PurchaseController::class, 'purchase']);
    Route::get('/checkout/success', function () {
        return redirect('/')->with('flashSuccess', '決済が完了しました！');
    })->name('checkout.success');
    Route::get('/checkout/cancel', function () {
        return redirect('/')->with('flashError', '決済がキャンセルされました');
    })->name('checkout.cancel');
    Route::get('/purchase/address/{item_id}',[PurchaseController::class, 'address']);
    Route::post('/purchase/address/{item_id}',[PurchaseController::class, 'updateAddress']);
    Route::get('/mypage', [UserController::class, 'mypage']);
    Route::get('/mypage/profile', [UserController::class, 'profile']);
    Route::post('/mypage/profile', [UserController::class, 'updateProfile']);
    Route::get('/transactions/{transaction}/chat', [TransactionController::class, 'showChat'])
        ->name('transactions.chat');
    Route::post('/transactions/{transaction}/chat', [ChatController::class, 'store'])
        ->name('chats.store');
    Route::put('/chats/{chat}', [ChatController::class, 'update'])->name('chats.update');
    Route::delete('/chats/{chat}', [ChatController::class, 'destroy'])->name('chats.destroy');
    Route::post('/transactions/{transaction}/complete', [TransactionController::class, 'complete'])
        ->name('transaction.complete');
    Route::post('/transactions/{transaction}/review', [ReviewController::class, 'store'])
        ->name('reviews.store');
});

Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware('email');
Route::post('/register', [RegisteredUserController::class, 'store']);

Route::get('/email/verify', function () {
    return view('auth.verify-email');
})->name('verification.notice');

Route::post('/email/verification-notification', function (Request $request) {
    session()->get('unauthenticated_user')->sendEmailVerificationNotification();
    session()->put('resent', true);
    return back()->with('message', 'Verification link sent!');
})->name('verification.send');

Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
    $request->fulfill();
    session()->forget('unauthenticated_user');
    return redirect('/mypage/profile');
})->name('verification.verify');
