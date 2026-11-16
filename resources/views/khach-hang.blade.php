<!DOCTYPE html>
<html lang="vi">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>Pet Clinic | Chăm sóc thú cưng</title>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
<link rel="stylesheet" href="{{ asset('css/khach-hang.css') }}?v=1">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-light bg-white sticky-top shadow-sm px-lg-5">
<a class="navbar-brand font-weight-bold text-primary" href="#"><i class="fas fa-paw mr-2"></i>PET CLINIC</a>
<button class="navbar-toggler" data-toggle="collapse" data-target="#mainNav"><span class="navbar-toggler-icon"></span></button>
<div class="collapse navbar-collapse" id="mainNav"><ul class="navbar-nav ml-auto align-items-lg-center">
<li class="nav-item"><a class="nav-link" href="#about">Về chúng tôi</a></li><li class="nav-item"><a class="nav-link" href="#services">Các dịch vụ</a></li>
<li class="nav-item"><a class="nav-link" href="#booking">Đặt lịch</a></li><li class="nav-item"><a class="nav-link" href="#lookup">Tra cứu lịch hẹn</a></li>
<li class="nav-item"><a class="nav-link" href="#contact">Liên hệ</a></li><li class="nav-item ml-lg-3"><a class="btn btn-outline-primary btn-sm px-3" href="{{ route('login') }}">Đăng nhập nhân viên</a></li>
</ul></div></nav>
<header class="hero"><div class="container"><div class="row align-items-center"><div class="col-lg-7"><span class="eyebrow">YÊU THƯƠNG · TẬN TÂM · CHUYÊN NGHIỆP</span><h1>Chăm sóc thú cưng<br><span>như thành viên gia đình</span></h1><p>Đồng hành cùng bạn trong hành trình chăm sóc sức khỏe và vẻ ngoài khỏe đẹp cho những người bạn bốn chân.</p><a href="#booking" class="btn btn-primary btn-lg mr-2">Đặt lịch ngay</a><a href="#services" class="btn btn-light btn-lg">Khám phá dịch vụ</a></div><div class="col-lg-5">
<div class="hero-visual">
  <div class="hero-photo"><img src="{{ asset('images/hero-pets.jpg') }}" alt="Chó và mèo được chăm sóc tại phòng khám"></div>
  <div class="hero-float hero-float-top"><span class="float-icon"><i class="fas fa-heart"></i></span><span><strong>Luôn tận tâm</strong><small>Vì sức khỏe của bé</small></span></div>
  <div class="hero-float hero-float-bottom"><span class="float-icon"><i class="fas fa-shield-alt"></i></span><span><strong>An toàn & chu đáo</strong><small>Đồng hành cùng chủ nuôi</small></span></div>
</div>
</div></div></div></header>
<div class="quick-strip">
  <div class="container">
    <div class="quick-item"><i class="fas fa-stethoscope"></i><span><strong>Khám & điều trị</strong><small>Quan tâm sức khỏe mỗi ngày</small></span></div>
    <div class="quick-item"><i class="fas fa-cut"></i><span><strong>Spa thú cưng</strong><small>Sạch sẽ, gọn gàng, thoải mái</small></span></div>
    <div class="quick-item"><i class="fas fa-calendar-check"></i><span><strong>Đặt lịch tiện lợi</strong><small>Chủ động chọn ngày và ca</small></span></div>
  </div>
</div>
<section id="about" class="section container"><div class="section-heading"><span>VỀ PET CLINIC</span><h2>Chăm sóc bằng cả trái tim</h2><p>Đội ngũ phòng khám hướng tới trải nghiệm thân thiện, an toàn và minh bạch cho thú cưng cùng chủ nuôi.</p></div><div class="row text-center"><div class="col-md-4"><div class="feature"><i class="fas fa-heartbeat"></i><h5>Quan tâm sức khỏe</h5><p>Tiếp nhận thông tin và nhu cầu chăm sóc rõ ràng.</p></div></div><div class="col-md-4"><div class="feature"><i class="fas fa-user-md"></i><h5>Đồng hành chuyên môn</h5><p>Hỗ trợ thăm khám và theo dõi tình trạng thú cưng.</p></div></div><div class="col-md-4"><div class="feature"><i class="fas fa-spa"></i><h5>Chăm sóc toàn diện</h5><p>Lựa chọn dịch vụ Spa phù hợp với bé nhà bạn.</p></div></div></div></section>
<section id="services" class="section soft"><div class="container"><div class="section-heading"><span>DỊCH VỤ</span><h2>Mọi nhu cầu trong một điểm đến</h2></div><div class="row"><div class="col-md-6"><div class="service-card"><div class="service-photo"><img src="{{ asset('images/service-medical.jpg') }}" alt="Bác sĩ thăm khám thú cưng"></div><div class="service-content"><i class="fas fa-stethoscope"></i><h4>Khám bệnh thú y</h4><p>Tiếp nhận thăm khám, ghi nhận dấu hiệu bất thường và nhu cầu ưu tiên của thú cưng.</p><a href="#booking">Đặt lịch khám <i class="fas fa-arrow-right"></i></a></div></div></div><div class="col-md-6"><div class="service-card"><div class="service-photo"><img src="{{ asset('images/service-grooming.jpg') }}" alt="Chăm sóc và làm đẹp thú cưng"></div><div class="service-content"><i class="fas fa-bath"></i><h4>Dịch vụ Spa</h4><p>Lựa chọn các dịch vụ chăm sóc, vệ sinh và làm đẹp theo danh mục của phòng khám.</p><a href="#booking">Đặt lịch Spa <i class="fas fa-arrow-right"></i></a></div></div></div></div></div></section>
<section id="booking" class="section container"><div class="section-heading"><span>ĐẶT LỊCH TRỰC TUYẾN</span><h2>Đặt lịch cho thú cưng</h2><p>Khách cũ nhập số điện thoại để tìm hồ sơ. Bạn có thể chọn bé đã có hoặc đăng ký thêm bé mới.</p></div>
<div class="booking-card"><div id="bookingMessage" class="alert d-none"></div><form id="bookingForm">
<div class="step-title">01 · Thông tin chủ nuôi</div><div class="form-row"><div class="form-group col-md-6"><label>Số điện thoại *</label><div class="input-group"><input id="phone" class="form-control" required maxlength="20" placeholder="Nhập số điện thoại"><div class="input-group-append"><button id="findOwner" type="button" class="btn btn-outline-primary">Tìm hồ sơ</button></div></div><small id="ownerHint" class="form-text text-muted">Nhập số điện thoại và bấm Tìm hồ sơ để tải thú cưng đã đăng ký.</small></div><div class="form-group col-md-6"><label>Họ tên chủ nuôi *</label><input id="ownerName" class="form-control" required></div><div class="form-group col-md-6"><label>Email</label><input id="ownerEmail" type="email" class="form-control"></div><div class="form-group col-md-6"><label>Địa chỉ</label><input id="ownerAddress" class="form-control"></div></div>
<div class="step-title">02 · Thú cưng</div><div class="form-group"><label>Chọn thú cưng đã có</label><select id="petSelect" class="form-control"><option value="">— Đăng ký thú cưng mới —</option></select></div>
<div id="newPetFields" class="form-row"><div class="form-group col-md-4"><label>Tên thú cưng *</label><input id="petName" class="form-control"></div><div class="form-group col-md-4"><label>Loài *</label><select id="species" class="form-control"><option value="Chó">Chó</option><option value="Mèo">Mèo</option><option value="Khác">Khác</option></select></div><div class="form-group col-md-4"><label>Giống</label><input id="breed" class="form-control"></div><div class="form-group col-md-4"><label>Giới tính</label><select id="gender" class="form-control"><option value="CHUA_XAC_DINH">Chưa xác định</option><option value="DUC">Đực</option><option value="CAI">Cái</option></select></div><div class="form-group col-md-4"><label>Ngày sinh</label><input id="dob" type="date" class="form-control"></div></div>
<div class="step-title">03 · Dịch vụ cần đặt</div>
<div class="form-group">
    <div class="custom-control custom-checkbox custom-control-inline">
        <input class="custom-control-input" type="checkbox" id="serviceSpa">
        <label class="custom-control-label" for="serviceSpa">Dịch vụ Spa</label>
    </div>

    <div class="custom-control custom-checkbox custom-control-inline">
        <input class="custom-control-input" type="checkbox" id="serviceMedical">
        <label class="custom-control-label" for="serviceMedical">Khám bệnh</label>
    </div>

    <small class="form-text text-muted">
        Có thể tích chọn một hoặc cả hai dịch vụ.
    </small>
</div>
<!-- Chỉ hiện khi tích chọn Khám bệnh -->
<div id="medicalFields" class="form-group d-none">
    <label>Ghi chú (Nếu tình trạng khám nặng)</label>
    <textarea id="medicalNote"
              class="form-control"
              rows="3"
              placeholder="Ví dụ: bỏ ăn, nôn, đi khập khiễng..."></textarea>
</div>

<!-- Chỉ hiện khi tích chọn Spa -->
<div id="spaFields" class="form-group d-none">
    <label>Chọn dịch vụ Spa (có thể chọn nhiều)</label>
    <div id="spaServices" class="spa-options">
        Đang tải danh mục dịch vụ...
    </div>
</div>
<div class="step-title">04 · Ngày và ca mong muốn</div><div class="form-row"><div class="form-group col-md-4"><label>Ngày hẹn *</label><input id="appointmentDate" type="date" class="form-control" required></div><div class="form-group col-md-4"><label>Ca *</label><select id="shift" class="form-control"><option value="SANG">Ca sáng (07:00–11:00)</option><option value="CHIEU">Ca chiều (14:00–17:00)</option></select></div><div class="form-group col-md-4"><label>Giờ mong muốn *</label><input id="preferredTime" type="time" class="form-control" value="08:00" required></div></div>
<button id="submitBooking" class="btn btn-primary btn-lg btn-block mt-3">Gửi yêu cầu đặt lịch</button><p class="text-muted small mt-2 mb-0">Lịch hẹn sẽ ở trạng thái chờ xác nhận. Giờ bạn chọn là thời gian mong muốn, phòng khám sẽ xác nhận lại.</p>
</form><div id="bookingSuccess" class="success-panel d-none"></div></div></section>
<section id="lookup" class="section soft"><div class="container"><div class="section-heading"><span>QUẢN LÝ LỊCH HẸN</span><h2>Tra cứu lịch hẹn</h2><p>Nhập số điện thoại đã dùng khi đặt lịch để xem các lịch hẹn và thông tin tương ứng.</p></div><div class="lookup-card"><div id="lookupMessage" class="alert d-none"></div><form id="lookupForm" class="form-row"><div class="form-group col-md-9"><label>Số điện thoại đã đăng ký</label><input id="lookupPhone" class="form-control" required maxlength="20" placeholder="Nhập số điện thoại"></div><div class="form-group col-md-3 d-flex align-items-end"><button class="btn btn-outline-primary btn-block">Tra cứu lịch</button></div></form><div id="lookupResults" class="lookup-results"></div><div id="bookingDetails" class="d-none">
<hr><h5 id="lookupPetName"></h5>
<div class="row mb-3">
<div class="col-md-6"><strong>Thông tin chủ nuôi</strong><div id="bookingOwnerInfo" class="text-muted small"></div></div>
<div class="col-md-6"><strong>Thông tin thú cưng</strong><div id="bookingPetInfo" class="text-muted small"></div></div>
</div>
<div class="alert alert-light py-2 mb-0">
<strong id="bookingServiceInfo"></strong><br>
<span id="bookingDateInfo" class="small text-muted"></span><br>
<span id="bookingStatus" class="small text-muted"></span><br>
<span id="bookingNoteInfo" class="small text-muted"></span>
</div>
</div></div></div></section>
<section id="contact" class="section container"><div class="contact-banner"><div><span class="eyebrow">PET CLINIC LUÔN SẴN SÀNG</span><h2>Mỗi bé đều xứng đáng được chăm sóc tốt nhất</h2><a href="#booking" class="btn btn-light">Đặt lịch cho bé <i class="fas fa-arrow-right ml-2"></i></a></div><img src="{{ asset('images/contact-pet.jpg') }}" alt="Thú cưng vui khỏe"></div><div class="section-heading"><span>LIÊN HỆ</span><h2>Chúng tôi luôn sẵn sàng hỗ trợ</h2></div><div class="row text-center"><div class="col-md-4"><i class="fas fa-map-marker-alt contact-icon"></i><h6>Địa chỉ</h6><p>Vui lòng cập nhật địa chỉ phòng khám tại đây.</p></div><div class="col-md-4"><i class="fas fa-phone contact-icon"></i><h6>Điện thoại</h6><p>Liên hệ trực tiếp với lễ tân phòng khám.</p></div><div class="col-md-4"><i class="fas fa-clock contact-icon"></i><h6>Giờ tiếp nhận</h6><p>Sáng 07:00–11:00 · Chiều 14:00–17:00</p></div></div></section>
<button type="button" id="backToTop" class="back-to-top" aria-label="Lên đầu trang" title="Lên đầu trang">
  <i class="fas fa-arrow-up"></i>
</button>
<footer class="footer text-center">© {{ date('Y') }} PET CLINIC · Chăm sóc thú cưng tận tâm</footer>
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script><script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script><script>
$(function () {
    var $navbar = $('.navbar');
    var $menu = $('#mainNav');

    // Hiển thị nội dung theo dịch vụ được chọn
    function toggleServiceFields() {
        const isMedical = $('#serviceMedical').is(':checked');
        const isSpa = $('#serviceSpa').is(':checked');

        $('#medicalFields').toggleClass('d-none', !isMedical);
        $('#spaFields').toggleClass('d-none', !isSpa);
    }

    // Khi thay đổi lựa chọn dịch vụ
    $('#serviceMedical, #serviceSpa').on('change', toggleServiceFields);

    // Khởi tạo trạng thái ban đầu
    toggleServiceFields();
    
    function scrollToSection(target) {
        var $target = $(target);
        if (!$target.length) return;

        var top = Math.max(0, $target.offset().top - $navbar.outerHeight() - 12);
        $('html, body').stop(true).animate({ scrollTop: top }, 450);
        if (history.replaceState) history.replaceState(null, '', target);
    }

    // Chọn mục trên menu mobile xong sẽ tự đóng menu rồi mới cuộn.
    $menu.find('a.nav-link[href^="#"]').on('click', function (event) {
        var target = $(this).attr('href');
        if (!target || target === '#' || !$(target).length) return;

        event.preventDefault();

        if ($menu.hasClass('show')) {
            $menu.one('hidden.bs.collapse', function () {
                scrollToSection(target);
            });
            $menu.collapse('hide');
        } else {
            scrollToSection(target);
        }
    });

    // Nút lên đầu trang chỉ xuất hiện sau khi cuộn xuống.
    var $backToTop = $('#backToTop');
    $(window).on('scroll', function () {
        $backToTop.toggleClass('is-visible', $(this).scrollTop() > 300);
    });
    $backToTop.on('click', function () {
        $('html, body').stop(true).animate({ scrollTop: 0 }, 500);
    });
});
</script>
<script src="{{ asset('js/khach-hang.js') }}?v=3"></script>
</body>
</html>