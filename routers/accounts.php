<?php
    $app->group(['prefix' => '/app', 'middleware' => 'auth'], function () use($app) {

        $app->router('/account', 'GET', ['App\Controllers\AccountController', 'Account']);

        $app->router('/profiles', 'GET', ['App\Controllers\AccountController', 'Profiles']);

        $app->router('/affiliate', 'GET', ['App\Controllers\AccountController', 'Affiliate']);
        
        $app->router('/affiliate/bank/add', 'POST', ['App\Controllers\AccountController', 'AddBankAccount']);
        
        $app->router('/affiliate/payout', 'POST', ['App\Controllers\AccountController', 'Payout']);

        $app->router('/payments', 'GET', ['App\Controllers\PaymentController', 'Payments']);
        
        $app->router('/payments/vnpay-return', 'GET', ['App\Controllers\PaymentController', 'VnpayReturn']);
        
        $app->router('/historys', 'GET', ['App\Controllers\AccountController', 'History']);

        $app->router('/account/change-infomation', 'POST', ['App\Controllers\AccountController', 'UpdateInformation']);
        
        $app->router('/uploads/scans', 'POST', ['App\Controllers\AccountController', 'uploadsPDF']);

        $app->router('/account/change-password', 'POST', ['App\Controllers\AccountController', 'ChangePassword']);
        
        $app->router('/account/deposit', 'POST', ['App\Controllers\PaymentController', 'Deposit']);
        
        $app->router('/wallet', 'POST', ['App\Controllers\AccountController', 'ConvertWalletToPoints']);
        
        $app->router('/report/ai', 'GET', ['App\Controllers\ReportController', 'ai']);
        
        $app->router('/report/original', 'GET', ['App\Controllers\ReportController', 'original']);
        
        $app->router('/historys/detail/{id}', 'GET', ['App\Controllers\AccountController', 'HistoryDetail']);

        $app->router('/historys/history-detail/{id}', 'GET', ['App\Controllers\AccountController', 'HistoryDetail']);

    });