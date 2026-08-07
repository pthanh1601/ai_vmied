<?php
    $app->group(['prefix' => '/admin', 'middleware' => 'admin'], function () use($app) {
        $app->router('/users', 'GET', ['App\Controllers\AdminController', 'Users']);
        $app->router('/users/set-vip', 'POST', ['App\Controllers\AdminController', 'SetVip']);
        $app->router('/users/add', 'POST', ['App\Controllers\AdminController', 'AddUser']);
        $app->router('/users/update', 'POST', ['App\Controllers\AdminController', 'UpdateUser']);
        $app->router('/users/delete', 'POST', ['App\Controllers\AdminController', 'DeleteUser']);
    });
