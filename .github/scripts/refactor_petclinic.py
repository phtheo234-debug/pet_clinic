#!/usr/bin/env python3
from pathlib import Path
import re

root = Path(".")
class_map = {
    "AdminCageController":"QuanLyLongController",
    "AdminInventoryController":"QuanLyKhoController",
    "AdminSpaServiceController":"QuanLyDichVuSpaController",
    "AdminUserController":"QuanLyNguoiDungController",
    "AuthController":"XacThucController",
    "BoardingController":"LuuTruController",
    "DashboardController":"ThongKeController",
    "InventoryController":"KhoController",
    "MedicalController":"KhamBenhController",
    "PaymentController":"ThanhToanController",
    "PetController":"ThuCungController",
    "SpaUsageController":"SuDungSpaController",
    "SurgeryController":"PhauThuatController",
    "VaccinationController":"TiemChungController",
    "CageOccupancyService":"DichVuSucChuaLong",
    "ForcePasswordChange":"BatBuocDoiMatKhau",
    "RoleMiddleware":"KiemTraVaiTro",
    "WebRoleMiddleware":"KiemTraVaiTroWeb",
    "WebTokenAuth":"XacThucTokenWeb",
    "PetBoarding":"LuotLuuTru",
    "Owner":"ChuNuoi",
    "Pet":"ThuCung",
    "Cage":"Long",
    "User":"NguoiDung",
}
js_map = {
    "admin-statistics.js":"thong_ke_quan_tri.js",
    "admin-users.js":"nguoi_dung_quan_tri.js",
    "auth.js":"xac_thuc.js",
    "cages.js":"long.js",
    "change-password.js":"doi_mat_khau.js",
    "config.js":"cau_hinh.js",
    "dashboard.js":"tong_quan.js",
    "forgot-password.js":"quen_mat_khau.js",
    "inventory.js":"kho.js",
    "invoices.js":"hoa_don.js",
    "medical.js":"kham_benh.js",
    "pets.js":"thu_cung.js",
    "profile.js":"ho_so.js",
}
view_map = {
    "cages.blade.php":"long.blade.php",
    "dashboard.blade.php":"tong_quan.blade.php",
    "medical.blade.php":"kham_benh.blade.php",
    "profile.blade.php":"ho_so.blade.php",
    "invoices.blade.php":"hoa_don.blade.php",
    "inventory.blade.php":"kho.blade.php",
    "pets.blade.php":"thu_cung.blade.php",
    "statistics.blade.php":"thong_ke.blade.php",
    "users.blade.php":"nguoi_dung.blade.php",
}
view_ids = {
    "profile":"ho_so", "dashboard":"tong_quan", "pets":"thu_cung",
    "cages":"long", "medical":"kham_benh", "inventory":"kho",
    "invoices":"hoa_don", "admin.users":"admin.nguoi_dung",
    "admin.statistics":"admin.thong_ke",
}
text_ext = {".php",".js",".css",".json",".md",".xml",".http",".sql",".txt",".yml",".yaml",".example"}
for f in list(root.rglob("*")):
    if not f.is_file() or (f.suffix.lower() not in text_ext and not f.name.endswith(".blade.php")):
        continue
    try:
        data = f.read_text(encoding="utf-8")
    except UnicodeDecodeError:
        continue
    original = data
    for old, new in sorted(class_map.items(), key=lambda x: -len(x[0])):
        data = re.sub(r"\b" + re.escape(old) + r"\b", new, data)
    for old, new in js_map.items():
        data = data.replace(old, new)
    if data != original:
        f.write_text(data, encoding="utf-8")

# PSR-4 class names and file names must match exactly.
for f in list((root / "app").rglob("*.php")):
    if f.stem in class_map:
        f.rename(f.with_name(class_map[f.stem] + ".php"))
factory = root / "database/factories/UserFactory.php"
if factory.exists():
    factory.rename(root / "database/factories/NguoiDungFactory.php")
    p = root / "database/factories/NguoiDungFactory.php"
    p.write_text(re.sub(r"\bUserFactory\b", "NguoiDungFactory", p.read_text(encoding="utf-8")), encoding="utf-8")

for f in list((root / "public/js").glob("*.js")):
    if f.name in js_map:
        f.rename(f.with_name(js_map[f.name]))
for f in list((root / "resources/views").rglob("*.blade.php")):
    if f.name in view_map:
        f.rename(f.with_name(view_map[f.name]))

for f in root.rglob("*.php"):
    data = f.read_text(encoding="utf-8")
    original = data
    for old, new in view_ids.items():
        data = data.replace(f"view('{old}')", f"view('{new}')")
        data = data.replace(f'view("{old}")', f'view("{new}")')
        data = data.replace(f", '{old}')", f", '{new}')")
        data = data.replace(f', "{old}")', f', "{new}")')
    data = data.replace("'admin.users'", "'admin.nguoi_dung'").replace("'admin.statistics'", "'admin.thong_ke'")
    if data != original:
        f.write_text(data, encoding="utf-8")
layout = root / "resources/views/layouts/app.blade.php"
if layout.exists():
    data = layout.read_text(encoding="utf-8").replace("admin.users", "admin.nguoi_dung").replace("admin.statistics", "admin.thong_ke")
    layout.write_text(data, encoding="utf-8")

css = root / "public/css/style.css"
if css.exists():
    css.rename(root / "public/css/giao_dien.css")
for f in list(root.rglob("*")):
    if f.is_file() and f.suffix.lower() in {".php",".js",".css",".md"}:
        try: data = f.read_text(encoding="utf-8")
        except UnicodeDecodeError: continue
        updated = data.replace("css/style.css", "css/giao_dien.css").replace("/style.css", "/giao_dien.css")
        if updated != data: f.write_text(updated, encoding="utf-8")

# Discard the historical chain of incremental business migrations.
migrations = root / "database/migrations"
keep = {
    "0001_01_01_000000_create_users_table.php",
    "0001_01_01_000001_create_cache_table.php",
    "0001_01_01_000002_create_jobs_table.php",
    "2026_11_05_034403_create_personal_access_tokens_table.php",
}
for f in migrations.glob("*.php"):
    if f.name not in keep:
        f.unlink()

baseline = (root / ".github/templates/tao_cac_bang_nghiep_vu.php").read_text(encoding="utf-8")
(migrations / "2026_11_15_000000_tao_cac_bang_nghiep_vu.php").write_text(baseline, encoding="utf-8")

seeder = """<?php

namespace Database\\Seeders;

use App\\Models\\NguoiDung;
use Illuminate\\Database\\Seeder;
use Illuminate\\Support\\Facades\\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Chi tao tai khoan quan tri khi nguoi van hanh tu khai bao day du bien moi truong.
     * Khong tao du lieu thu, vaccine, phau thuat hoac tai khoan mau mac dinh.
     */
    public function run(): void
    {
        $username = env('ADMIN_USERNAME');
        $name = env('ADMIN_NAME');
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (!$username || !$name || !$email || !$password) {
            return;
        }

        NguoiDung::firstOrCreate(
            ['username' => $username],
            [
                'ho_ten' => $name,
                'email' => $email,
                'mat_khau' => Hash::make($password),
                'vaitro' => 'QUAN_TRI',
                'trangthai' => 'HOAT_DONG',
                'phai_doimatkhau' => true,
                'ngaytao' => now(),
            ]
        );
    }
}
"""
(root / "database/seeders/DatabaseSeeder.php").write_text(seeder, encoding="utf-8")

env_file = root / ".env.example"
env = env_file.read_text(encoding="utf-8")
env = env.replace("APP_NAME=Laravel", "APP_NAME=PetClinic")
env = env.replace("APP_LOCALE=en", "APP_LOCALE=vi").replace("APP_FALLBACK_LOCALE=en", "APP_FALLBACK_LOCALE=vi")
env = env.replace("DB_DATABASE=pkthucung", "DB_DATABASE=pet_clinic")
env += "\n# Optional: create the initial administrator when running php artisan db:seed\nADMIN_USERNAME=\nADMIN_NAME=\nADMIN_EMAIL=\nADMIN_PASSWORD=\n"
env_file.write_text(env, encoding="utf-8")

(root / "pk_thu_cung.sql").unlink(missing_ok=True)
readme = """# Pet Clinic

He thong quan ly phong kham thu y, xay dung bang Laravel 12, PHP 8.2+, MySQL, Blade, Bootstrap 4 va JavaScript.

## Cong nghe
- Backend: Laravel 12, PHP 8.2+, Eloquent ORM, Laravel Sanctum.
- CSDL: MySQL.
- Giao dien: Blade, Bootstrap 4.6.2, jQuery, Font Awesome, Chart.js.
- Frontend: JavaScript thuan, Fetch API.

## Cai dat
1. Cai PHP 8.2+, Composer, MySQL va cac extension Laravel yeu cau.
2. Chay 'composer install'.
3. Sao chep '.env.example' thanh '.env', sau do cau hinh ket noi MySQL.
4. Chay 'php artisan key:generate'.
5. Tao CSDL 'pet_clinic' (hoac doi 'DB_DATABASE' trong '.env').
6. Chay 'php artisan migrate'.
7. (Tuy chon) Khai bao 'ADMIN_USERNAME', 'ADMIN_NAME', 'ADMIN_EMAIL', 'ADMIN_PASSWORD' trong '.env', sau do chay 'php artisan db:seed' de tao tai khoan quan tri ban dau. Khong co mat khau mau mac dinh.
8. Chay 'php artisan serve'.

## Du lieu danh muc
Migration chi tao cau truc bang. Khong chen du lieu gia nhu "Vac-xin 1..6", khong tu tao vat tu/thuoc gia 0 dong, va khong tu nap danh muc phau thuat mau. Danh muc duoc quan tri vien them tu giao dien quan ly.

## CSDL va migrations
Cac migration nghiep vu da duoc gom lai thanh mot migration khoi tao schema hien tai: 'database/migrations/2026_11_15_000000_tao_cac_bang_nghiep_vu.php'. Cac migration Laravel/Sanctum mac dinh duoc giu lai. CSDL moi can chay 'php artisan migrate' tu dau.

Luu y: bo migration moi nay danh cho CSDL moi cua repository 'pet_clinic'; khong chay 'migrate:fresh' tren CSDL cu neu con du lieu can giu. Cac ten bang va cot hien tai duoc giu nguyen de tranh lam vo cac truy van va API dang dung.

## Quy uoc ten
Cac controller, model, middleware, service, view va JavaScript do du an tao da duoc chuyen sang ten tieng Viet khong dau. Cac ten va duong dan bat buoc cua Laravel/Composer duoc giu nguyen.
"""
(root / "README.md").write_text(readme, encoding="utf-8")
