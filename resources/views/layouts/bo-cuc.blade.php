<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Hệ thống Quản lý Phòng khám Thú cưng')</title>

    <link rel="stylesheet"
          href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

    <link rel="stylesheet"
          href="{{ asset('css/giao-dien.css') }}?v={{ filemtime(public_path('css/giao-dien.css')) }}">
</head>

<body>

    @php
        $currentUser = request()->attributes->get('web_authenticated_user');
        $role = strtoupper(trim($currentUser->vaitro ?? ''));
    @endphp

    <div class="wrapper">

        {{-- SIDEBAR --}}
        <nav id="sidebar">

            <div class="sidebar-header">

                <h5 class="mb-0 font-weight-bold text-info">
                    <i class="fas fa-clinic-medical mr-2"></i>
                    PET CLINIC
                </h5>

                <small class="text-muted">
                    Hệ thống Quản lý Thú y
                </small>

            </div>

            <ul class="list-unstyled components">

                {{-- Thông tin tài khoản --}}
                <li class="{{ request()->routeIs('ho-so-ca-nhan') ? 'active' : '' }}">
                    <a href="{{ route('ho-so-ca-nhan') }}">
                        <i class="fas fa-user-cog"></i>
                        Thông tin tài khoản
                    </a>
                </li>


                {{-- Chỉ quản trị viên --}}
                @if($role === 'QUAN_TRI')

                    <li class="{{ request()->routeIs('admin.tai-khoan') ? 'active' : '' }}">
                        <a href="{{ route('admin.tai-khoan') }}">
                            <i class="fas fa-users-cog"></i>
                            Quản lý tài khoản
                        </a>
                    </li>

                    <li class="{{ request()->routeIs('admin.thong-ke') ? 'active' : '' }}">
                        <a href="{{ route('admin.thong-ke') }}">
                            <i class="fas fa-chart-pie"></i>
                            Thống kê doanh thu
                        </a>
                    </li>

                @endif


                {{-- Tổng quan --}}
                <li class="{{ request()->routeIs('tong-quan') ? 'active' : '' }}">
                    <a href="{{ route('tong-quan') }}">
                        <i class="fas fa-chart-line"></i>
                        Tổng quan (Dashboard)
                    </a>
                </li>


                {{-- Chủ nuôi và thú cưng --}}
                <li class="{{ request()->routeIs('thu-cung') ? 'active' : '' }}">
                    <a href="{{ route('thu-cung') }}">
                        <i class="fas fa-paw"></i>
                        Chủ nuôi & Thú cưng
                    </a>
                </li>


                {{-- Lồng lưu trú: tất cả vai trò được xem --}}
                <li class="{{ request()->routeIs('long-nuoi') ? 'active' : '' }}">
                    <a href="{{ route('long-nuoi') }}">
                        <i class="fas fa-home"></i>
                        Lồng lưu trú nội trú
                    </a>
                </li>


                {{-- Bác sĩ và quản trị viên --}}
                @if(in_array($role, ['QUAN_TRI', 'BAC_SI'], true))

                    <li class="{{ request()->routeIs('kham-benh') ? 'active' : '' }}">
                        <a href="{{ route('kham-benh') }}">
                            <i class="fas fa-stethoscope"></i>
                            Khám bệnh & Kê đơn
                        </a>
                    </li>

                @endif


                {{-- Quản lý các kho hàng --}}
                <li class="{{ request()->routeIs('kho') ? 'active' : '' }}">
                    <a href="{{ route('kho') }}">
                        <i class="fas fa-pills"></i>
                        Quản lý kho
                    </a>
                </li>


                {{-- Nhân viên và quản trị viên --}}
                @if(in_array($role, ['QUAN_TRI', 'NHAN_VIEN'], true))

                    <li class="{{ request()->routeIs('lich-hen-khach') ? 'active' : '' }}">
                        <a href="{{ route('lich-hen-khach') }}"><i class="fas fa-calendar-check"></i> Lịch hẹn khách hàng</a>
                    </li>

                    <li class="{{ request()->routeIs('hoa-don') ? 'active' : '' }}">
                        <a href="{{ route('hoa-don') }}">
                            <i class="fas fa-file-invoice-dollar"></i>
                            Thanh toán POS
                        </a>
                    </li>

                @endif

            </ul>

        </nav>


        {{-- NỘI DUNG CHÍNH --}}
        <div id="content">

            <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm px-4">

                <span class="navbar-brand font-weight-bold text-dark mb-0">
                    @yield('page_title')
                </span>

                <div class="ml-auto d-flex align-items-center">

                    <span class="mr-3 text-secondary">

                        <i class="fas fa-user-circle mr-1 text-primary"></i>

                        <strong id="navUserName">
                            Đang tải...
                        </strong>

                        <span id="navUserRole"
                              class="badge badge-info ml-1">
                        </span>

                    </span>

                    <button onclick="logout()"
                            class="btn btn-outline-danger btn-sm">

                        <i class="fas fa-sign-out-alt"></i>
                        Đăng xuất

                    </button>

                </div>

            </nav>


            {{-- NỘI DUNG TỪ CÁC TRANG --}}
            <div class="container-fluid p-4">

                @yield('content')

            </div>

        </div>

    </div>


    {{-- JAVASCRIPT --}}
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>

    <script src="{{ asset('js/cau-hinh.js') }}"></script>

    {{-- Script riêng của từng trang --}}
    @stack('scripts')

</body>
</html>
