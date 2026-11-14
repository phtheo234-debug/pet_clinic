<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::view('/login', 'auth.dang-nhap')->name('login');
Route::view('/forgot-password', 'auth.quen-mat-khau')->name('forgot-password');

Route::middleware('web.token')->group(function () {
    Route::view('/change-password', 'auth.doi-mat-khau')->name('change-password');
    Route::view('/profile', 'ho-so-ca-nhan')->name('ho-so-ca-nhan');

    // Các vai trò đều được xem tổng quan, hồ sơ thú cưng và danh sách lồng.
    Route::view('/dashboard', 'tong-quan')->name('tong-quan');
    Route::view('/pets', 'thu-cung')->name('thu-cung');
    Route::view('/cages', 'long-nuoi')->name('long-nuoi');

    // Bác sĩ và quản trị viên được truy cập nghiệp vụ khám, chẩn đoán, kê đơn.
    Route::view('/medical', 'kham-benh')
        ->middleware('web.role:QUAN_TRI,BAC_SI')
        ->name('kham-benh');

    // Cả ba vai trò được xem danh mục kho; thao tác nhập/xuất và quản lý
    // danh mục được kiểm soát riêng tại API.
    Route::view('/inventory', 'kho')->name('kho');

    // Thanh toán, xuất viện và ghi nhận Spa dành cho nhân viên/quản trị.
    Route::view('/invoices', 'hoa-don')
        ->middleware('web.role:QUAN_TRI,NHAN_VIEN')
        ->name('hoa-don');

    Route::view('/admin/users', 'admin.tai-khoan')
        ->middleware('web.role:QUAN_TRI')
        ->name('admin.tai-khoan');

    Route::view('/admin/statistics', 'admin.thong-ke')
        ->middleware('web.role:QUAN_TRI')
        ->name('admin.thong-ke');
});

