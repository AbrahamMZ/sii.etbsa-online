<?php

Route::prefix('admin')->namespace('Admin')->middleware(['auth'])->group(function () {

    // resource
    Route::resource('agency-bank-accounts', 'AgencyBankAccountController');
    Route::resource('polizas', 'PolizaController');
    Route::put('polizas/{poliza}/apply', 'PolizaController@apply')->name('polizas.apply');
    Route::put('polizas/{poliza}/unapply', 'PolizaController@unapply')->name('polizas.unapply');

});