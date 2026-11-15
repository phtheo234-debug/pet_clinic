# Pet Clinic

He thong quan ly phong kham thu cung, xay dung bang Laravel 12, PHP 8.2+, MySQL/MariaDB, Blade, Bootstrap 4 va JavaScript.

## Cai dat

1. Cai PHP 8.2+, Composer va MySQL/MariaDB.
2. Tao database rong `pet_clinic`, charset `utf8mb4`.
3. Chay `composer install`.
4. Sao chep `.env.example` thanh `.env`, cap nhat thong tin ket noi MySQL.
5. Chay `php artisan key:generate`.
6. Chay `php artisan migrate`.
7. Chay `php artisan serve` va mo `http://127.0.0.1:8000`.

## Tao tai khoan quan tri dau tien

Khong con tai khoan mau voi mat khau mac dinh. Sau khi migrate, chay `php artisan tinker` va tao tai khoan voi mat khau rieng:

```php
\\App\\Models\\NguoiDung::create([
    'username' => 'admin',
    'mat_khau' => \\Illuminate\\Support\\Facades\\Hash::make('THAY_BANG_MAT_KHAU_RIENG'),
    'ho_ten' => 'Quan tri vien',
    'email' => 'admin@example.com',
    'phone' => null,
    'vaitro' => 'QUAN_TRI',
    'trangthai' => 'HOAT_DONG',
    'phai_doimatkhau' => true,
    'ngaytao' => now(),
]);
```

## Co so du lieu

Cac bang nghiep vu dung ten tieng Viet khong dau: `nguoi_dung`, `chu_nuoi`, `thu_cung`, `long_nuoi`, `lich_su_can_nang`, `luot_luu_tru`, `yeu_cau_luu_tru`, `phieu_kham`, `hang_doi_kham`, `danh_muc_phau_thuat`, `kho_hang`, `lo_hang`, `don_thuoc`, `hoa_don`, `chi_tiet_hoa_don`, `luot_dich_vu_spa`, `loai_vac_xin`, `lich_su_tiem_chung`.

Cac bang ha tang Laravel (`cache`, `jobs`, `sessions`, `personal_access_tokens`...) duoc giu lai de tuong thich cau hinh framework. Migration khong tu chen du lieu gia cho danh muc vac-xin, phau thuat, mat hang kho hoac tai khoan.

Day la bo migration khoi tao cho database moi. Khong chay `migrate:fresh` tren database dang co du lieu can giu. Database cu can sao luu va chuyen du lieu rieng.

Ten file va class do du an tao duoc doi sang tieng Viet khong dau. Ten bat buoc cua Laravel/Composer duoc giu nguyen. URL API duoc giu nguyen de khong lam dut cac request tu giao dien.

## Website khach hang va dat lich

- Trang chu cong khai: `/`; dang nhap nhan vien: `/login`.
- Khach hang tim ho so bang so dien thoai, chon thu cung cu hoac tao ho so thu cung moi.
- Ho tro dat lich kham, Spa hoac ca hai; chon ngay, ca sang/chieu va gio mong muon.
- Yeu cau kham duoc dua vao `hang_doi_kham`; `created_by` cho phep NULL de phan biet lich hen tu khach hang voi luot do nhan vien tiep nhan.
- Tra cuu lich hen bang so dien thoai; neu co nhieu lich, khach chon lich can xem. Chi lich dang cho xac nhan moi duoc chinh sua truc tuyen.
- Ngay hen va ngay doi lich phai tu ngay hom sau tro di; backend tu kiem tra, khong chi gioi han tren giao dien.
- Nhan vien/le tan va quan tri xem, xac nhan, huy lich tai `/appointments`; co the hoan tat sau khi ca kham da o trang thai `DA_KHAM` hoac sau khi hoan tat dich vu Spa.
- Danh muc Spa tren trang khach hang lay truc tiep tu cac mat hang co `danhmuc = DICH_VU_SPA` trong `kho_hang`.

Sau khi cap nhat ma nguon, chay:

```bash
php artisan migrate
php artisan optimize:clear
```

Khong dung `migrate:fresh` tren database dang co du lieu.
