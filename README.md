# Pet Clinic

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
