<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Thêm ràng buộc số điện thoại duy nhất cho tài khoản.
     * Cho phép nhiều tài khoản để trống số điện thoại (NULL).
     */
    public function up(): void
    {
        Schema::table('nguoi_dung', function (Blueprint $table) {
            $table->unique('phone', 'nguoi_dung_phone_unique');
        });
    }

    /**
     * Gỡ ràng buộc số điện thoại duy nhất.
     */
    public function down(): void
    {
        Schema::table('nguoi_dung', function (Blueprint $table) {
            $table->dropUnique('nguoi_dung_phone_unique');
        });
    }
};
