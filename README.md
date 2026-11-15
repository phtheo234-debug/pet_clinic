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

Cac bang nghiep vu dung ten tieng Viet khong dau: `nguoi_dung`, `chu_nuoi`, `thu_cung`, `long_nuoi`, `luot_luu_tru`, `yeu_cau_luu_tru`, `phieu_kham`, `hang_doi_kham`, `lich_hen_khach`, `danh_muc_phau_thuat`, `kho_hang`, `lo_hang`, `don_thuoc`, `hoa_don`, `chi_tiet_hoa_don`, `luot_dich_vu_spa`, `loai_vac_xin`, `lich_su_tiem_chung`.

Can nang hien tai duoc luu tai `thu_cung.cannang`; khong tao bang lich su can nang. Nguong canh bao ton kho la co dinh `<= 5` don vi; dich vu Spa khong quan ly so luong ton. Khong su dung cac cot cau hinh lieu `lieu_mg_kg`, `mg_moi_don_vi`.

Cac bang ha tang Laravel (`cache`, `jobs`, `sessions`, `personal_access_tokens`...) duoc giu lai de tuong thich cau hinh framework. Migration khong tu chen du lieu gia cho danh muc vac-xin, phau thuat, mat hang kho hoac tai khoan.

Day la bo migration khoi tao cho database moi. Khong chay `migrate:fresh` tren database dang co du lieu can giu. Database cu can sao luu va chuyen du lieu rieng.

Ten file va class do du an tao duoc doi sang tieng Viet khong dau. Ten bat buoc cua Laravel/Composer duoc giu nguyen. URL API duoc giu nguyen de khong lam dut cac request tu giao dien.

## Website khach hang va dat lich

- Trang chu cong khai: `/`; dang nhap nhan vien: `/login`.
- Khach hang tim ho so bang so dien thoai, chon thu cung cu hoac tao ho so thu cung moi.
- Ho tro dat lich kham, Spa hoac ca hai; chon ngay, ca sang/chieu va gio mong muon.
- Lich hen duoc tu dong xac nhan (`DA_XAC_NHAN`) ngay khi dat thanh cong; khong can le tan duyet.
- Neu chon dich vu kham (`MEDICAL`/`BOTH`), he thong tao/gan hang doi `CHO_KHAM`; khi phieu kham hoan tat, lich hen loai `MEDICAL` chuyen `DA_HOAN_THANH`.
- Trang thai lich hen: `DA_XAC_NHAN`, `DA_HOAN_THANH`, `DA_HUY`.
- Tra cuu lich hen bang so dien thoai; neu co nhieu lich, khach chon lich can xem. Lich hen duoc tu dong xac nhan ngay khi dat thanh cong; khach hang chi tra cuu thong tin.
- Ngay hen va ngay doi lich phai tu ngay hom sau tro di; backend tu kiem tra, khong chi gioi han tren giao dien.
- Yeu cau dat lich van duoc luu trong `lich_hen_khach`; khong con trang quan ly lich hen rieng trong khu vuc noi bo.
- Danh muc Spa tren trang khach hang lay truc tiep tu cac mat hang co `danhmuc = DICH_VU_SPA` trong `kho_hang`.

## Ho so chu nuoi va thu cung

- Trang `/pets` hien thi danh sach ho so thu cung, tim kiem theo so dien thoai va goi y so dien thoai da co.
- Form tiep nhan co tuy chon `Kham benh`; neu tich, he thong tu tao luot `CHO_KHAM`, ghi chu tiep nhan la tuy chon.
- Trang thai kham duoc tinh tu hang doi: khong co luot kham la `KHONG_KHAM`; hang doi su dung `CHO_KHAM`, `DANG_KHAM`, `DA_KHAM`. Phieu kham chi luu thong tin kham va huong xu ly, khong luu mot trang thai kham trung lap. Trang thai luu tru (`NOI_TRU`, `NGOAI_TRU`, `DA_XUAT_VIEN`, `CHUYEN_VIEN`) van duoc quan ly rieng.
- Ho so chu nuoi/thu cung khong co chuc nang sua/xoa; cap nhat truc tiep truong `thu_cung.cannang`; `phieu_kham.cannangkhi_kham` la anh chup can nang tai thoi diem kham.
- Ho so co lich hen kham con hieu luc duoc uu tien tren dau; ghi chu lich hen duoc hien truoc ghi chu tiep nhan.

Sau khi cap nhat ma nguon, chay:

```bash
php artisan migrate
php artisan optimize:clear
```

Khong dung `migrate:fresh` tren database dang co du lieu.


## Cap nhat dat lich va luu tru

- Form dat lich tren trang chu dung hai o tich chon doc lap: **Dich vu spa** va **Kham benh**; co the chon mot hoac ca hai.
- Tra cuu lich hen bang so dien thoai chi hien thi thong tin, khong cho sua lich.
- Phi luu tru la **50.000 VND/ngay** cho tat ca cac loai long; gia xem truoc va hoa don dung cung mot muc phi.
- Trang long luu tru co nut **Thong tin luu tru** khi long dang co thu, hien ma/ten thu cung va ten/so dien thoai chu nuoi.
- Long dang co thu khong the sua; long co thu hoac da co lich su luu tru khong the xoa.
