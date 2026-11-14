<?php

namespace Database\Seeders;

use App\Models\NguoiDung;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $username = env('ADMIN_USERNAME');
        $name = env('ADMIN_NAME');
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (!$username || !$name || !$email || !$password) return;

        NguoiDung::firstOrCreate(['username' => $username], [
            'ho_ten' => $name, 'email' => $email,
            'mat_khau' => Hash::make($password), 'vaitro' => 'QUAN_TRI',
            'trangthai' => 'HOAT_DONG', 'phai_doimatkhau' => true, 'ngaytao' => now(),
        ]);
    }
}
