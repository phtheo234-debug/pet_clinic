# 🐾 PET CLINIC – Hệ thống quản lý phòng khám thú cưng

PET CLINIC là ứng dụng web hỗ trợ quản lý hoạt động của phòng khám thú cưng, từ tiếp nhận hồ sơ, khám bệnh, kê đơn, quản lý kho, tiêm chủng, lưu trú, dịch vụ Spa đến thanh toán và thống kê. Hệ thống cũng cung cấp giao diện công khai để khách hàng tra cứu hồ sơ và đặt lịch dịch vụ.

---

## 📌 Công nghệ sử dụng

| Thành phần | Công nghệ |
|---|---|
| Backend | PHP 8.2+, Laravel 12 |
| Giao diện | Blade, HTML, CSS, Bootstrap, JavaScript |
| Cơ sở dữ liệu | MySQL / MariaDB |
| Xác thực API | Laravel Sanctum |
| Quản lý phụ thuộc | Composer |

## ✨ Chức năng chính

### 1. Khu vực khách hàng
- Tra cứu chủ nuôi bằng số điện thoại.
- Chọn thú cưng đã có hoặc tạo hồ sơ thú cưng mới khi đặt lịch.
- Đặt lịch khám bệnh, dịch vụ Spa hoặc kết hợp cả hai.
- Chọn ngày hẹn, ca sáng/chiều và giờ mong muốn.
- Tra cứu lịch hẹn bằng số điện thoại.
- Xem thông tin và hủy lịch đã xác nhận nếu lượt khám chưa bắt đầu.

**Lưu ý:** Lịch hẹn được xác nhận tự động sau khi đặt thành công. Ngày hẹn phải từ ngày hôm sau trở đi.

### 2. Quản lý hồ sơ chủ nuôi và thú cưng
- Tiếp nhận và quản lý thông tin chủ nuôi, thú cưng.
- Tìm kiếm hồ sơ theo số điện thoại.
- Ghi nhận cân nặng hiện tại và lưu cân nặng tại thời điểm khám.
- Tạo lượt chờ khám trong quá trình tiếp nhận khi khách có nhu cầu khám bệnh.
- Theo dõi trạng thái khám thông qua hàng đợi khám.

### 3. Quản lý khám bệnh
- Theo dõi danh sách chờ khám.
- Bác sĩ bắt đầu lượt khám và lập phiếu khám.
- Ghi nhận thông tin khám, chẩn đoán và hướng xử lý.
- Kê đơn thuốc và kiểm tra số lượng tồn kho.
- Tra cứu lịch sử khám của thú cưng.
- Theo dõi lịch tiêm chủng và lịch sử tiêm.

### 4. Quản lý kho
- Quản lý danh mục thuốc, vắc-xin, thức ăn, đồ dùng và dịch vụ Spa.
- Theo dõi số lượng tồn kho.
- Nhập, xuất và cập nhật thông tin mặt hàng theo quyền được cấp.
- Kiểm tra số lượng tồn trước khi xuất dùng hoặc bán.
- Cảnh báo mặt hàng có số lượng tồn không quá 5 đơn vị.

**Cách quản lý tồn kho hiện tại:** Số lượng được lưu trực tiếp tại `kho_hang.soluongton`. Hệ thống chưa quản lý theo lô, số lô, ngày sản xuất, hạn sử dụng và không áp dụng FEFO.

### 5. Quản lý lưu trú
- Quản lý danh sách lồng nuôi.
- Tiếp nhận yêu cầu lưu trú và xác nhận xếp lồng.
- Theo dõi lượt lưu trú, tình trạng lồng và thông tin thú cưng đang ở.
- Tính phí lưu trú 50.000 VND/ngày.
- Không cho phép xóa lồng đang có thú cưng hoặc đã có lịch sử lưu trú.

### 6. Quản lý dịch vụ Spa
- Quản lý danh mục dịch vụ Spa.
- Ghi nhận dịch vụ Spa đã thực hiện cho thú cưng.
- Cho phép khách hàng chọn dịch vụ Spa khi đặt lịch.

### 7. Thanh toán và hóa đơn
- Tổng hợp chi phí khám, thuốc, Spa, lưu trú và các khoản liên quan.
- Xem trước chi phí trước khi thanh toán.
- Tạo hóa đơn thanh toán.
- Hỗ trợ thanh toán dịch vụ và bán hàng tại quầy.

### 8. Quản trị hệ thống
- Quản lý tài khoản người dùng.
- Phân quyền theo vai trò.
- Khóa/mở trạng thái tài khoản và đặt lại mật khẩu.
- Quản lý danh mục nghiệp vụ.
- Xem thống kê tổng quan và báo cáo doanh thu.

## 👥 Vai trò người dùng

| Vai trò | Quyền nghiệp vụ chính |
|---|---|
| `QUAN_TRI` – Quản trị viên | Quản lý tài khoản, danh mục, kho, lồng nuôi; truy cập các nghiệp vụ khám, thanh toán và thống kê |
| `BAC_SI` – Bác sĩ | Xem hồ sơ thú cưng, thực hiện khám bệnh, chẩn đoán, kê đơn và tra cứu lịch sử khám |
| `NHAN_VIEN` – Nhân viên | Tiếp nhận thú cưng, quản lý lưu trú, ghi nhận Spa, xuất kho và xử lý thanh toán |

Quyền truy cập được kiểm soát ở cả giao diện web và các API tương ứng.

---

## 🧰 Yêu cầu môi trường

Trước khi cài đặt, cần chuẩn bị:

- PHP **8.2 trở lên**.
- Composer.
- MySQL hoặc MariaDB.
- Git (nếu clone dự án từ GitHub).
- Các extension PHP cần thiết cho Laravel và kết nối MySQL đã được bật.

## 🚀 Cài đặt và chạy dự án

### Bước 1. Lấy mã nguồn

Clone repository:

```bash
git clone https://github.com/phtheo234-debug/pet_clinic.git
cd pet_clinic
```

Nếu cần sử dụng nhánh hoàn thiện nghiệp vụ:

```bash
git switch hoan-thien-nghiep-vu-pet-clinic
git pull origin hoan-thien-nghiep-vu-pet-clinic
```

### Bước 2. Cài đặt thư viện

```bash
composer install
```

### Bước 3. Tạo file cấu hình môi trường

Sao chép `.env.example` thành `.env`:

**Windows CMD:**
```cmd
copy .env.example .env
```

**PowerShell:**
```powershell
Copy-Item .env.example .env
```

Mở file `.env` và cấu hình kết nối MySQL. Ví dụ:

```env
APP_NAME="Pet Clinic"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=pet_clinic
DB_USERNAME=root
DB_PASSWORD=
```

Tạo database rỗng tên `pet_clinic` trong MySQL, sử dụng charset `utf8mb4`.

### Bước 4. Tạo khóa ứng dụng

```bash
php artisan key:generate
```

### Bước 5. Khởi tạo cơ sở dữ liệu

```bash
php artisan migrate
```

Các migration tạo cấu trúc bảng. Dự án không tự động thêm tài khoản quản trị hoặc dữ liệu mẫu cho danh mục nghiệp vụ.

### Bước 6. Tạo tài khoản quản trị đầu tiên

Chạy Tinker:

```bash
php artisan tinker
```

Nhập đoạn lệnh sau và thay email, số điện thoại, mật khẩu bằng thông tin riêng:

```php
\App\Models\NguoiDung::create([
    'username' => 'admin',
    'mat_khau' => \Illuminate\Support\Facades\Hash::make('THAY_BANG_MAT_KHAU_RIENG'),
    'ho_ten' => 'Quản trị viên',
    'email' => 'admin@example.com',
    'phone' => 'THAY_BANG_SO_DIEN_THOAI_RIENG',
    'vaitro' => 'QUAN_TRI',
    'trangthai' => 'HOAT_DONG',
    'phai_doimatkhau' => true,
    'ngaytao' => now(),
]);
```

Thoát Tinker:

```php
exit
```

Tài khoản được yêu cầu đổi mật khẩu theo cờ `phai_doimatkhau` khi đăng nhập lần đầu.

### Bước 7. Chạy ứng dụng

```bash
php artisan serve
```

Mở trình duyệt tại:

- Website: http://127.0.0.1:8000
- Đăng nhập nhân viên: http://127.0.0.1:8000/login

---

## 🗂️ Cấu trúc thư mục

| Thư mục / Tệp | Mục đích |
|---|---|
| `app/Http/Controllers` | Xử lý nghiệp vụ và API |
| `app/Models` | Các model dữ liệu |
| `app/Http/Middleware` | Kiểm tra xác thực, vai trò và điều kiện truy cập |
| `database/migrations` | Định nghĩa cấu trúc cơ sở dữ liệu |
| `database/seeders` | Seeder của dự án |
| `resources/views` | Giao diện Blade |
| `public` | Tài nguyên tĩnh CSS, JavaScript và hình ảnh |
| `routes/web.php` | Các tuyến giao diện web |
| `routes/api.php` | Các tuyến API |

## 🌐 Một số đường dẫn chính

| Đường dẫn | Chức năng |
|---|---|
| `/` | Trang chủ khách hàng |
| `/login` | Đăng nhập nhân viên |
| `/dashboard` | Tổng quan hệ thống |
| `/pets` | Hồ sơ thú cưng |
| `/cages` | Quản lý lồng nuôi |
| `/medical` | Khám bệnh |
| `/inventory` | Quản lý kho |
| `/invoices` | Hóa đơn |
| `/admin/users` | Quản lý tài khoản |
| `/admin/statistics` | Thống kê |

API được tổ chức dưới tiền tố `/api/v1`. Các API nghiệp vụ nội bộ yêu cầu xác thực; một số API đặt lịch và tra cứu công khai không yêu cầu đăng nhập.

## 🗄️ Thông tin cơ sở dữ liệu

Các bảng nghiệp vụ chính gồm:

`nguoi_dung`, `chu_nuoi`, `thu_cung`, `long_nuoi`, `kho_hang`, `danh_muc_phau_thuat`, `loai_vac_xin`, `lich_su_tiem_chung`, `phieu_kham`, `hang_doi_kham`, `don_thuoc`, `hoa_don`, `chi_tiet_hoa_don`, `luot_luu_tru`, `yeu_cau_luu_tru`, `luot_dich_vu_spa`, `lich_hen_khach`.

Một số quy ước dữ liệu hiện tại:

- Cân nặng hiện tại của thú cưng được lưu tại `thu_cung.cannang`.
- Số lượng tồn kho được lưu trực tiếp tại `kho_hang.soluongton`.
- Số điện thoại người dùng có ràng buộc duy nhất.
- Ghi chú tiếp nhận được lưu tại `hang_doi_kham.ghi_chu_tiep_nhan`.
- Các bảng hạ tầng Laravel như `cache`, `jobs`, `sessions` và `personal_access_tokens` được giữ lại.

## 🔄 Cập nhật mã nguồn

Sau khi kéo thay đổi mới từ Git:

```bash
git pull
composer install
php artisan migrate
php artisan optimize:clear
```

Nếu có thay đổi cấu hình môi trường, kiểm tra lại file `.env` trước khi chạy ứng dụng.

> **Cảnh báo:** `php artisan migrate:fresh` sẽ xóa toàn bộ bảng hiện có trong database được cấu hình. Chỉ sử dụng với database thử nghiệm hoặc khi chắc chắn không cần giữ dữ liệu. Không chạy lệnh này trên dữ liệu đang sử dụng.

## 🧪 Kiểm tra

Chạy bộ kiểm thử của Laravel:

```bash
php artisan test
```

---

## 📄 Ghi chú

- Đây là dự án học tập/thực tập xây dựng hệ thống quản lý phòng khám thú cưng.
- Không đưa file `.env`, mật khẩu, khóa ứng dụng hoặc thông tin truy cập cơ sở dữ liệu lên GitHub.
- Khi triển khai môi trường thật, cần cấu hình `APP_DEBUG=false`, HTTPS, sao lưu cơ sở dữ liệu và thiết lập tài khoản theo chính sách bảo mật.
