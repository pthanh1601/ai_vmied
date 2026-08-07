<?php
    $app->group(['prefix' => '/mock/originality'], function ($app) {
    
        // 1. Kiểm tra tiền (GET /account/balance)
        $app->router('/account/balance', 'GET', ['App\Controllers\MockOriginalityController', 'getBalance']);

        // 2. Scan Text (POST /scan)
        $app->router('/scan', 'POST', ['App\Controllers\MockOriginalityController', 'scan']);

        // 3. Scan URL (POST /scan/url)
        $app->router('/scan/url', 'POST', ['App\Controllers\MockOriginalityController', 'scanUrl']);

        // 4. Batch Scan (POST /scan/batch)
        $app->router('/scan/batch', 'POST', ['App\Controllers\MockOriginalityController', 'scanBatch']);

        // 5. Get Result by ID (GET /scan/{id})
        $app->router('/scan/{id}', 'GET', ['App\Controllers\MockOriginalityController', 'getScanById']);

        // 6. Get Scan History (GET /history)
        $app->router('/history', 'GET', ['App\Controllers\MockOriginalityController', 'history']);
        
        $app->router('/upload-pdf', 'POST', ['App\Controllers\MockOriginalityController', 'uploadPdf']);

        // Get cost before scan
        $app->router('/cost', 'POST', ['App\Controllers\MockOriginalityController', 'estimateCost']);
        
        $app->router('/scan/{id}/plagiarism', 'GET', ['App\Controllers\MockOriginalityController', 'getPlagiarism']);
    });