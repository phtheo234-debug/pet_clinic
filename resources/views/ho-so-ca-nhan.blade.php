@extends('layouts.bo-cuc')
@section('title', 'Thông tin tài khoản - Pet Clinic')
@section('page_title', 'Thông tin tài khoản')
@section('content')
<div class="row"><div class="col-lg-7"><div class="card shadow-sm"><div class="card-header bg-white font-weight-bold">Thông tin cá nhân</div><div class="card-body">
<div id="profileAlert" class="alert d-none"></div><form id="profileForm">
<div class="form-group"><label>Tên đăng nhập</label><input id="profileUsername" class="form-control" readonly></div>
<div class="form-group"><label>Họ và tên</label><input id="profileName" class="form-control" required maxlength="100"></div>
<div class="form-group"><label>Email</label><input id="profileEmail" type="email" class="form-control" required></div>
<div class="form-group"><label>Số điện thoại</label><input id="profilePhone" class="form-control" maxlength="20"></div>
<button class="btn btn-primary">Lưu thông tin</button></form></div></div></div>
<div class="col-lg-5"><div class="card shadow-sm"><div class="card-header bg-white font-weight-bold">Đổi mật khẩu</div><div class="card-body">
<div id="passwordAlert" class="alert d-none"></div><form id="profilePasswordForm">
<div class="form-group">
<label for="oldPassword">Mật khẩu hiện tại</label>
<div class="input-group">
<input id="oldPassword" type="password" class="form-control" autocomplete="current-password" required>
<div class="input-group-append">
<button type="button" class="btn btn-outline-secondary" onclick="togglePassword('oldPassword', this)" aria-label="Hiện mật khẩu" title="Hiện mật khẩu"><i class="fas fa-eye"></i></button>
</div>
</div>
</div>
<div class="form-group">
<label for="profileNewPassword">Mật khẩu mới</label>
<div class="input-group">
<input id="profileNewPassword" type="password" class="form-control" minlength="6" autocomplete="new-password" required>
<div class="input-group-append">
<button type="button" class="btn btn-outline-secondary" onclick="togglePassword('profileNewPassword', this)" aria-label="Hiện mật khẩu" title="Hiện mật khẩu"><i class="fas fa-eye"></i></button>
</div>
</div>
</div>
<div class="form-group">
<label for="profileConfirmPassword">Nhập lại mật khẩu mới</label>
<div class="input-group">
<input id="profileConfirmPassword" type="password" class="form-control" minlength="6" autocomplete="new-password" required>
<div class="input-group-append">
<button type="button" class="btn btn-outline-secondary" onclick="togglePassword('profileConfirmPassword', this)" aria-label="Hiện mật khẩu" title="Hiện mật khẩu"><i class="fas fa-eye"></i></button>
</div>
</div>
</div>
<button class="btn btn-success">Đổi mật khẩu</button></form></div></div></div></div>
@endsection
@push('scripts')<script src="{{ asset('js/ho-so-ca-nhan.js') }}"></script>@endpush