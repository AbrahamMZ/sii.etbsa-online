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

Route::get('/', 'Front\\HomeController@index')->name('front.home');
Route::get('files/{id}/preview', 'Front\\FileController@filePreview')->name('front.file.preview');
Route::get('files/{id}/download', 'Front\\FileController@fileDownload')->name('front.file.download');

Auth::routes();

// NOTE:
// remove the demo middleware before you start on a project, this middleware if only
// for demo purpose to prevent viewers to modify data on a live demo site

// admin
Route::prefix('admin')->namespace('Admin')->middleware(['auth'])->group(function () {
    // single page
    Route::get('/', 'SinglePageController@displaySPA')->name('admin.spa');

    // resource routes
    Route::resource('users', 'UserController');
    Route::resource('sellers', 'SellerController');

    Route::resource('groups', 'GroupController');
    Route::resource('permissions', 'PermissionController');
    Route::resource('files', 'FileController');
    Route::resource('file-groups', 'FileGroupController');

    Route::resource('prospects', 'ProspectController');
    Route::resource('tracking', 'TrackingProspectController');
    Route::get('tracking/sales_history/resources', 'TrackingProspectController@resources')->name('trackingResources');
    // Route::put('tracking/assignSeller/{id}', 'TrackingProspectController@assignSeller')->name('trackingAssignSeller');
    Route::put('tracking/historical/{id}', 'TrackingProspectController@addHistoricalTracking')->name('tracking.addHistoricalTracking');
    Route::put('tracking/resetToActive/{id}', 'TrackingProspectController@resetToActive')->name('tracking.resetToActive');
    Route::get('tracking/historical/diary', 'TrackingProspectController@diaryTrackings')->name('tracking.diary');
    Route::get('tracking-export', 'ExportController@exportTracking')->name('trackingExport');
    Route::post('tracking/message', 'MessageTrackingController@store')->name('message.tracking.store');
    Route::get('tracking/messages/{tracking}', 'MessageTrackingController@getMessagesTracking')->name('message.tracking.getMessagesTracking');

    //Notification
    Route::get('notification', 'NotificationController@index')->name('notification.index');
    Route::delete('notification/{notification}', 'NotificationController@destroy')->name('notification.destroy');

    //Marketing
    Route::get('marketing/sales_history', 'MarketingController@salesHistory')->name('salesHistory');
    Route::get('marketing/sales_history/resources', 'MarketingController@resources')->name('salesHistoryResources');
    Route::get('marketing/export', 'ExportController@exportMarketing')->name('marketingExport');

    // ResourcesController
    Route::get('resource/agencies', 'ResourcesShareController@getAgencies')->name('resources.agencies');
    Route::get('resource/users', 'ResourcesShareController@getUser')->name('resources.users');
});

//Clear Cache facade value:
Route::get('/clear-cache', function () {
    $exitCode = Artisan::call('cache:clear');
    return '<h1>Cache facade value cleared</h1>';
});

//Reoptimized class loader:
Route::get('/optimize', function () {
    $exitCode = Artisan::call('optimize');
    return '<h1>Reoptimized class loader</h1>';
});

//Route cache:
Route::get('/route-cache', function () {
    $exitCode = Artisan::call('route:cache');
    return '<h1>Routes cached</h1>';
});

//Clear Route cache:
Route::get('/route-clear', function () {
    $exitCode = Artisan::call('route:clear');
    return '<h1>Route cache cleared</h1>';
});

//Clear View cache:
Route::get('/view-clear', function () {
    $exitCode = Artisan::call('view:clear');
    return '<h1>View cache cleared</h1>';
});

//Clear Config cache:
Route::get('/config-cache', function () {
    $exitCode = Artisan::call('config:cache');
    return '<h1>Clear Config cleared</h1>';
});
