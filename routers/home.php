<?php
$app->group(['prefix' => '/app', 'middleware' => 'auth'], function () use ($app) {

    // ===== PAGES =====
    $app->router('', 'GET', ['App\Controllers\AuthController', 'dashboard']);

    $app->router('/ai', 'GET', function () use ($app) {
        $user = $app->request->user;
        return view('ai/ai', ['user' => $user]);
    });
    $app->router('/plagiarism', 'GET', function () use ($app) {
        $user = $app->request->user;
        return view('ai/plagiarism', ['user' => $user]);
    });
    $app->router('/grammar', 'GET', function () use ($app) {
        $user = $app->request->user;
        return view('ai/grammar', ['user' => $user]);
    });
    $app->router('/humanizer', 'GET', function () use ($app) {
        $user = $app->request->user;
        return view('ai/humanizer', ['user' => $user]);
    });


    // ===== QUẢN LÝ THÀNH VIÊN (DÀNH CHO VIP) =====
    $app->router('/members', 'GET', ['App\Controllers\AccountController', 'Members']);
    $app->router('/members/add', 'POST', ['App\Controllers\AccountController', 'AddMember']);
    $app->router('/members/update', 'POST', ['App\Controllers\AccountController', 'UpdateMember']);
    $app->router('/members/delete', 'POST', ['App\Controllers\AccountController', 'DeleteMember']);
    $app->router('/members/toggle-status', 'POST', ['App\Controllers\AccountController', 'ToggleMemberStatus']);

    // AI Detection → dùng /scan
    $app->router('/ai/scan', 'POST', ['App\Controllers\MockOriginalityController', 'scan']);

    $app->router('/plagiarism/scan', 'POST', ['App\Controllers\MockOriginalityController', 'scan']);

    $app->router('/grammar/scan', 'POST', ['App\Controllers\MockOriginalityController', 'scan']);

    $app->router('/humanizer/scan', 'POST', ['App\Controllers\MockOriginalityController', 'scan']);

});