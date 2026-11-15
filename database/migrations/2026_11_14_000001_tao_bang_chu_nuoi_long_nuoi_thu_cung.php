<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chu_nuoi', function (Blueprint $table) {
            $table->id('owner_id');
            $table->string('ho_ten', 100);
            $table->string('phone', 20)->unique();
            $table->string('diachi', 255)->nullable();
            $table->string('email', 100)->nullable();
            $table->timestamp('ngaytao')->useCurrent();
        });

        Schema::create('long_nuoi', function (Blueprint $table) {
            $table->id('cage_id');
            $table->string('malong', 20)->unique();
            $table->string('ten_long', 50);
            $table->enum('loailong', ['THUONG', 'CAP_CUU_ICU', 'CACH_LY'])->default('THUONG');
            $table->unsignedInteger('succhua')->default(1);
            $table->unsignedInteger('soluonghientai')->default(0);
            $table->decimal('tai_trong_toi_da', 7, 2)->nullable();
            $table->string('loai_ap_dung', 30)->nullable();
            $table->enum('trangthai', ['CON_TRONG', 'DA_CO_THU', 'DANG_VE_SINH', 'BAO_TRI'])->default('CON_TRONG');
        });

        Schema::create('thu_cung', function (Blueprint $table) {
            $table->id('pet_id');
            $table->string('mathucung', 20)->unique();
            $table->foreignId('owner_id')->constrained('chu_nuoi', 'owner_id')->cascadeOnDelete();
            $table->foreignId('cage_id')->nullable()->constrained('long_nuoi', 'cage_id')->nullOnDelete();
            $table->string('ten_thucung', 50);
            $table->string('loai', 30);
            $table->string('giong', 50)->nullable();
            $table->decimal('cannang', 5, 2)->nullable();
            $table->enum('gioitinh', ['DUC', 'CAI', 'CHUA_XAC_DINH'])->default('CHUA_XAC_DINH');
            $table->date('ngaysinh')->nullable();
            $table->enum('trangthai', ['NOI_TRU', 'NGOAI_TRU', 'DA_XUAT_VIEN', 'CHUYEN_VIEN'])->default('NGOAI_TRU');
            $table->timestamp('ngaytao')->useCurrent();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('thu_cung');
        Schema::dropIfExists('long_nuoi');
        Schema::dropIfExists('chu_nuoi');
    }
};
