<?php
    $app->router('/', 'GET', function () use ($app) {
        $sessionUser = $app->session->get('account');
        
        // Lấy danh sách các Trường/Đơn vị (VIP Affiliate)
        $vip_affiliates = $app->db->select("accounts", ["uuid", "organization", "avatar", "affiliate"], [
            "type" => 2,
            "status" => 1,
            "deleted" => 0,
            "organization[!]" => "",
            "avatar[!]" => ""
        ]);

        return view('home', [
            'title' => 'Ai Vmied',
            'user' =>  $sessionUser,
            'vip_affiliates' => $vip_affiliates
        ]);
    });
    $app->router('/login', 'GET', ['App\Controllers\AuthController', 'index']);
    $app->router('/login', 'POST', ['App\Controllers\AuthController', 'Login']);
    $app->router('/register', 'POST', ['App\Controllers\AuthController', 'Register']);
    $app->router('/logout', 'GET', ['App\Controllers\AuthController', 'Logout']);
