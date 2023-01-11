<?php

use Illuminate\Http\Request;

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
header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token, Authorization, Accept,charset,boundary,Content-Length');
header('Access-Control-Allow-Origin: *');

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('v1')->group(function() {
    // Route::middleware(['cors'])->group(function() {
        Route::get('/products', 'ApiController@getProducts')->name('api-get-products-info'); // it's working well
        Route::post('/login', 'ApiController@login')->name('api-login');
        Route::post('/register', 'ApiController@register')->name('api-register');
        Route::post('/order', 'ApiController@orderSubmit')->name('api-order-submit');
        Route::post('/yedpayOrder', 'ApiController@yedpayOrderSubmit')->name('api-yedpayOrder-submit');
        Route::get('/prices', 'ApiController@getStoragePeriodItem')->name('api-prices');
        Route::post('/promo-code', 'ApiController@promotionCodeValidate')->name('promo-code');

        Route::prefix('/client')->group(function() {
            Route::post('/getUser', 'ApiController@fetchUser')->name('api-getUser');
            Route::post('/account/update', 'ApiController@clientUpdate')->name('account-update');
            Route::post('/changePassword', 'ApiController@ChangePassword')->name('change-password');
            Route::post('/updateOrder', 'ApiController@updateOrder')->name('update-order');
            Route::post('/getOrders', 'ApiController@getOrders')->name('get-orders');
            Route::post('/fetchCurrentOrder', 'ApiController@fetchCurrentOrder')->name('fetch-current-order');
            Route::post('/emailOtpForgotPassword', 'ApiController@sendEmailOtp')->name('forgot-password-email-otp');
            Route::post('/resetPassword', 'ApiController@resetPassword')->name('reset-password');
        });

        Route::prefix('/admin')->group(function() {
            Route::post('/auth/login', 'ApiAdminController@authAdmin')->name('admin-login');
            Route::post('fetchUniversities', 'ApiAdminController@index')->name('admin-dashboard');
            Route::post('fetchProducts', 'ApiAdminController@fetchProducts');
            Route::post('fetchRef', 'ApiAdminController@fetchRef');

            Route::post('fetchClients', 'ApiAdminController@fetchClients')->name('clients-list');
            Route::post('deleteClient', 'ApiAdminController@deleteClient')->name('delete-client');
            Route::post('editClient', 'ApiAdminController@editClient')->name('edit-client');

            Route::post('fetchPeriods', 'ApiAdminController@fetchPeriods')->name('storage-period-list');
            Route::post('editPeriod', 'ApiAdminController@editPeriod')->name('edit-period');
            Route::post('editPeriodItem', 'ApiAdminController@editPeriodItem');
            Route::post('deletePeriod', 'ApiAdminController@deletePeriod')->name('delete-period');

            Route::post('fetchPayments', 'ApiAdminController@fetchPayments')->name('payment-list');
            Route::post('editPayment', 'ApiAdminController@editPayment')->name('edit-payment');
            Route::post('deletePayment', 'ApiAdminController@deletePayment')->name('delete-payment');
            Route::post('payment/cancelled', 'ApiAdminController@paymentCancelled')->name('payment-cancelled');
            Route::post('payment/paid', 'ApiAdminController@paymentPaid')->name('payment-paid');

            Route::post('fetchPromotions', 'ApiAdminController@fetchPromotions')->name('promotions-list');
            Route::post('editPromotion', 'ApiAdminController@editPromotion')->name('edit-promotion');
            Route::post('editPromotionItem', 'ApiAdminController@editPromotionItem')->name('edit-promotionItem');
            Route::post('deletePromotion', 'ApiAdminController@deletePromotion')->name('delete-promotions');

            Route::post('fetchOrders', 'ApiAdminController@fetchOrders')->name('orders-list');
            Route::post('editOrder', 'ApiAdminController@editOrder')->name('edit-order');
            Route::post('deleteOrder', 'ApiAdminController@deleteOrder')->name('delete-order');
            Route::post('sendInvoice', 'ApiAdminController@sendInvoice')->name('send-invoice');

        });
});