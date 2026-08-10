<?php
$app->group(['prefix' => '/admin', 'middleware' => 'admin'], function () use($app) {
    // 1. Quản lý Đối tác VIP (Chỉ Admin)
    $app->router('/vips', 'GET', ['App\Controllers\AdminController', 'Vips']);
    
    // 2. Quản lý Thành viên (Admin xem tất cả, VIP xem của mình)
    $app->router('/members', 'GET', ['App\Controllers\AdminController', 'Members']);
    
    // 3. Xem lịch sử quét của thành viên
    $app->router('/members/history', 'GET', ['App\Controllers\AdminController', 'MemberHistory']);
    
    // 4. Xem chi phí của Admin
    $app->router('/statistics', 'GET', ['App\Controllers\AdminController', 'Statistics']);
    
    //action duyệt tài khoản Vips
    $app->router('/vips/approve', 'POST', ['App\Controllers\AdminController', 'ApproveVip']);

    // Các action thêm/sửa/xóa giữ nguyên nhưng xử lý quyền bên trong Controller
    $app->router('/users/add', 'POST', ['App\Controllers\AdminController', 'AddUser']);
    $app->router('/users/update', 'POST', ['App\Controllers\AdminController', 'UpdateUser']);
    $app->router('/users/delete', 'POST', ['App\Controllers\AdminController', 'DeleteUser']);
    $app->router('/users/toggle-status', 'POST', ['App\Controllers\AdminController', 'ToggleStatus']);
    
    // Route để view danh sách
    $app->router('/admins', 'GET', ['App\Controllers\AdminController', 'Admins']);
    $app->router('/roles', 'GET', ['App\Controllers\AdminController', 'Roles']);
    $app->router('/user-finance', 'GET', ['App\Controllers\AdminController', 'UserFinanceHistory']);

    // Route xử lý API Nhóm quyền (Roles)
    $app->router('/roles/add', 'POST', ['App\Controllers\AdminController', 'AddRole']);
    $app->router('/roles/update', 'POST', ['App\Controllers\AdminController', 'UpdateRole']);
    $app->router('/roles/delete', 'POST', ['App\Controllers\AdminController', 'DeleteRole']);
});