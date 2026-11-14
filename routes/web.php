<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::view('/login', 'auth.login')->name('login');
Route::view('/forgot-password', 'auth.forgot-password')->name('forgot-password');

Route::middleware('web.token')->group(function () {
    Route::view('/change-password', 'auth.change-password')->name('change-password');
    Route::view('/profile', 'ho_so')->name('profile');

    // Các vai trò đều được xem tổng quan, hồ sơ thú cưng và danh sách lồng.
    Route::view('/dashboard', 'tong_quan')->name('dashboard');
    Route::view('/pets', 'thu_cung')->name('pets');
    Route::view('/cages', 'long')->name('cages');

    // Bác sĩ và quản trị viên được truy cập nghiệp vụ khám, chẩn đoán, kê đơn.
    Route::view('/medical', 'kham_benh')
        ->middleware('web.role:QUAN_TRI,BAC_SI')
        ->name('medical');

    // Cả ba vai trò được xem danh mục kho; thao tác nhập/xuất và quản lý
    // danh mục được kiểm soát riêng tại API.
    Route::view('/inventory', 'kho')->name('inventory');

    // Thanh toán, xuất viện và ghi nhận Spa dành cho nhân viên/quản trị.
    Route::view('/invoices', 'hoa_don')
        ->middleware('web.role:QUAN_TRI,NHAN_VIEN')
        ->name('invoices');

    Route::view('/admin/users', 'admin.nguoi_dung')
        ->middleware('web.role:QUAN_TRI')
        ->name('admin.nguoi_dung');

    Route::view('/admin/statistics', 'admin.thong_ke')
        ->middleware('web.role:QUAN_TRI')
        ->name('admin.thong_ke');
});

Route::view('/test-api', 'test-api');
