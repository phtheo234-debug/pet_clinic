<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\XacThucController;
use App\Http\Controllers\Api\QuanLyTaiKhoanController;
use App\Http\Controllers\Api\QuanLyLongController;
use App\Http\Controllers\Api\QuanLyKhoController;
use App\Http\Controllers\Api\QuanLyDichVuSpaController;
use App\Http\Controllers\Api\TongQuanController;
use App\Http\Controllers\Api\ThuCungController;
use App\Http\Controllers\Api\LuuTruController;
use App\Http\Controllers\Api\KhoController;
use App\Http\Controllers\Api\KhamBenhController;
use App\Http\Controllers\Api\ThanhToanController;
use App\Http\Controllers\Api\SuDungDichVuSpaController;
use App\Http\Controllers\Api\PhauThuatController;
use App\Http\Controllers\Api\TiemChungController;
use App\Http\Controllers\KhachHangController;

/*
|--------------------------------------------------------------------------
| PUBLIC ROUTES
|--------------------------------------------------------------------------
*/

Route::get('/v1/public/owners', [KhachHangController::class, 'timChuNuoi'])->middleware('throttle:20,1');
Route::get('/v1/public/spa-services', [KhachHangController::class, 'dichVuSpa'])->middleware('throttle:30,1');
Route::post('/v1/public/bookings', [KhachHangController::class, 'datLich'])->middleware('throttle:8,1');
Route::get('/v1/public/bookings/lookup', [KhachHangController::class, 'traCuu'])->middleware('throttle:15,1');
Route::put('/v1/public/bookings/update', [KhachHangController::class, 'capNhat'])->middleware('throttle:8,1');

Route::post('/v1/auth/login', [XacThucController::class, 'login'])->middleware('throttle:5,1');
Route::post('/v1/auth/forgot-password', [XacThucController::class, 'forgotPassword'])->middleware('throttle:3,1');
Route::post('/v1/auth/reset-password', [XacThucController::class, 'resetForgottenPassword'])->middleware('throttle:5,1');

/*
|--------------------------------------------------------------------------
| PROTECTED ROUTES
|--------------------------------------------------------------------------
*/

Route::middleware(['auth:sanctum', 'force.password.change'])->prefix('v1')->group(function () {
    Route::get('/users/me', [XacThucController::class, 'me']);
    Route::put('/users/profile', [XacThucController::class, 'updateProfile']);
    Route::put('/users/change-password', [XacThucController::class, 'changePassword']);
    Route::post('/auth/logout', [XacThucController::class, 'logout']);

    // QUAN_TRI: quản lý tài khoản và danh mục hệ thống.
    Route::prefix('admin')->middleware('role:QUAN_TRI')->group(function () {
        Route::get('/users', [QuanLyTaiKhoanController::class, 'index']);
        Route::get('/users/{id}', [QuanLyTaiKhoanController::class, 'show']);
        Route::post('/users', [QuanLyTaiKhoanController::class, 'store']);
        Route::put('/users/{id}', [QuanLyTaiKhoanController::class, 'update']);
        Route::delete('/users/{id}', [QuanLyTaiKhoanController::class, 'destroy']);
        Route::put('/users/{id}/reset-password', [QuanLyTaiKhoanController::class, 'resetPassword']);
        Route::put('/users/{id}/status', [QuanLyTaiKhoanController::class, 'updateStatus']);

        Route::post('/cages', [QuanLyLongController::class, 'store']);
        Route::put('/cages/{id}', [QuanLyLongController::class, 'update']);
        Route::delete('/cages/{id}', [QuanLyLongController::class, 'destroy']);


        Route::post('/inventory', [QuanLyKhoController::class, 'store']);
        Route::put('/inventory/{id}', [QuanLyKhoController::class, 'update']);
        Route::delete('/inventory/{id}', [QuanLyKhoController::class, 'destroy']);
        Route::delete('/inventory/batches/{id}', [KhoController::class, 'deleteExpiredBatch']);

        Route::get('/surgeries', [PhauThuatController::class, 'index']);
        Route::post('/surgeries', [PhauThuatController::class, 'store']);
        Route::put('/surgeries/{id}', [PhauThuatController::class, 'update']);
        Route::delete('/surgeries/{id}', [PhauThuatController::class, 'destroy']);

        Route::post('/spa-services', [QuanLyDichVuSpaController::class, 'store']);
        Route::put('/spa-services/{id}', [QuanLyDichVuSpaController::class, 'update']);
        Route::delete('/spa-services/{id}', [QuanLyDichVuSpaController::class, 'destroy']);
    });

    // Hồ sơ thú cưng: mọi vai trò được xem; nhân viên/quản trị tiếp nhận và xóa;
    // cập nhật thông tin/cân nặng được phép cho cả bác sĩ.
    Route::get('/pets', [ThuCungController::class, 'index']);
    Route::get('/pets/{pet}', [ThuCungController::class, 'show']);
    Route::post('/pets', [ThuCungController::class, 'store'])->middleware('role:QUAN_TRI,NHAN_VIEN');
    Route::put('/pets/{pet}', [ThuCungController::class, 'update'])->middleware('role:QUAN_TRI,BAC_SI,NHAN_VIEN');
    Route::patch('/pets/{pet}', [ThuCungController::class, 'update'])->middleware('role:QUAN_TRI,BAC_SI,NHAN_VIEN');
    Route::delete('/pets/{pet}', [ThuCungController::class, 'destroy'])->middleware('role:QUAN_TRI');
    Route::get('/owners', [ThuCungController::class, 'owners']);
    Route::put('/owners/{id}', [ThuCungController::class, 'updateOwner'])->middleware('role:QUAN_TRI,NHAN_VIEN');
    Route::delete('/owners/{id}', [ThuCungController::class, 'destroyOwner'])->middleware('role:QUAN_TRI');

    // Lịch tiêm chủng: xem lịch và lịch sử; ghi nhận tiêm được thực hiện cùng phiếu khám.
    Route::get('/vaccinations/types', [TiemChungController::class, 'types']);
    Route::get('/vaccinations/schedule', [TiemChungController::class, 'schedule']);
    Route::get('/pets/{pet}/vaccinations', [TiemChungController::class, 'petIndex']);

    // Lồng: tất cả được xem; hệ thống đề xuất, nhân viên/quản trị xác nhận xếp lồng.
    Route::get('/boarding/cages', [LuuTruController::class, 'index']);
    Route::get('/boarding/requests', [LuuTruController::class, 'requests']);
    Route::post('/boarding/requests/{id}/confirm', [LuuTruController::class, 'confirmRequest'])
        ->middleware('role:QUAN_TRI,NHAN_VIEN');

    // Quản lý lịch hẹn trực tuyến của khách hàng.
    Route::middleware('role:QUAN_TRI,NHAN_VIEN')->group(function () {
        Route::get('/appointments', [KhachHangController::class, 'danhSachLichHen']);
        Route::patch('/appointments/{id}/status', [KhachHangController::class, 'capNhatTrangThai']);
    });

    // Ghi nhận Spa và thanh toán thuộc nghiệp vụ nhân viên/quản trị.
    Route::get('/spa/usages', [SuDungDichVuSpaController::class, 'index'])->middleware('role:QUAN_TRI,NHAN_VIEN');
    Route::post('/spa/usages', [SuDungDichVuSpaController::class, 'store'])->middleware('role:QUAN_TRI,NHAN_VIEN');

    Route::get('/surgeries', [PhauThuatController::class, 'index']);

    // Danh mục kho cho phép xem; nhập/xuất kho dành cho nhân viên/quản trị.
    Route::get('/inventory', [KhoController::class, 'index']);
    Route::post('/inventory/batch/import', [KhoController::class, 'importBatch'])->middleware('role:QUAN_TRI,NHAN_VIEN');
    Route::post('/inventory/export', [KhoController::class, 'exportMedicineFEFO'])->middleware('role:QUAN_TRI,NHAN_VIEN');

    // Chỉ bác sĩ/quản trị được lập hồ sơ khám và kê đơn.
    Route::middleware('role:QUAN_TRI,BAC_SI')->group(function () {
        Route::post('/medical/records', [KhamBenhController::class, 'store']);
        Route::get('/medical/records', [KhamBenhController::class, 'index']);
        Route::get('/medical/queue', [KhamBenhController::class, 'queue']);
        Route::post('/medical/queue/{id}/start', [KhamBenhController::class, 'startQueueRecord']);
        Route::get('/medical/records/{id}', [KhamBenhController::class, 'show']);
        Route::post('/medical/prescriptions', [KhamBenhController::class, 'createPrescription']);
        Route::get('/medical/history/pet/{petId}', [KhamBenhController::class, 'getHistoryByPet']);
    });

    // Hóa đơn, xuất viện và ghi chú ưu tiên khám dành cho nhân viên/quản trị.
    Route::middleware('role:QUAN_TRI,NHAN_VIEN')->group(function () {
        Route::post('/medical/queue', [KhamBenhController::class, 'addToQueue']);
        Route::put('/medical/queue/{id}/note', [KhamBenhController::class, 'updateQueueNote']);
        Route::get('/payment/eligible-pets', [ThanhToanController::class, 'eligiblePets']);
        Route::get('/payment/invoices', [ThanhToanController::class, 'index']);
        Route::get('/payment/pets/{petId}/preview', [ThanhToanController::class, 'previewPetCharges']);
        Route::post('/payment/checkout', [ThanhToanController::class, 'createUnifiedInvoice']);
        Route::get('/payment/invoices/{id}', [ThanhToanController::class, 'show']);
        Route::get('/payment/visits/pending', [ThanhToanController::class, 'pendingVisits']);
        Route::post('/payment/visits/checkout', [ThanhToanController::class, 'createVisitInvoice']);
        Route::post('/payment/pos/checkout', [ThanhToanController::class, 'createRetailInvoice']);
    });

    Route::get('/analytics/dashboard', [TongQuanController::class, 'getOverview']);
    Route::get('/analytics/inventory-alerts', [TongQuanController::class, 'getInventoryAlerts'])
        ->middleware('role:QUAN_TRI,NHAN_VIEN');
    Route::get('/analytics/revenue', [TongQuanController::class, 'getRevenueReport'])
        ->middleware('role:QUAN_TRI');
});
