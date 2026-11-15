@extends('layouts.bo-cuc')

@section('title', 'Lịch hẹn khách hàng - Pet Clinic')
@section('page_title', 'Tiếp nhận lịch hẹn trực tuyến')

@section('content')
<div class="card shadow-sm">
    <div class="card-header bg-white d-flex justify-content-between align-items-center">
        <div><strong>Danh sách lịch hẹn</strong><div class="small text-muted">Yêu cầu từ website khách hàng, bao gồm khám bệnh và Spa.</div></div>
        <button class="btn btn-outline-primary btn-sm" id="refreshBookings"><i class="fas fa-sync-alt mr-1"></i>Làm mới</button>
    </div>
    <div class="card-body">
        <div id="bookingStaffMessage" class="alert d-none"></div>
        <div class="table-responsive">
            <table class="table table-hover table-sm">
                <thead class="thead-light"><tr><th>Mã lịch</th><th>Chủ nuôi</th><th>Thú cưng</th><th>Dịch vụ</th><th>Ngày / Ca</th><th>Giờ mong muốn</th><th>Ghi chú</th><th>Trạng thái</th><th>Thao tác</th></tr></thead>
                <tbody id="bookingStaffRows"><tr><td colspan="9" class="text-center text-muted">Đang tải lịch hẹn...</td></tr></tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/lich-hen-khach.js') }}?v=1"></script>
@endpush
