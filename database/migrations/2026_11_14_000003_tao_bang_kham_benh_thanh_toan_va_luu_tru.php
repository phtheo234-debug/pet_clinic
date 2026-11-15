<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hoa_don', function (Blueprint $table) {
            $table->id('invoice_id');
            $table->string('mahoadon', 20)->unique();
            $table->foreignId('pet_id')->constrained('thu_cung', 'pet_id');
            $table->foreignId('owner_id')->constrained('chu_nuoi', 'owner_id');
            $table->decimal('tongtien', 12, 2);
            $table->enum('phuongthucthanhtoan', ['TIEN_MAT', 'CHUYEN_KHOAN', 'THE'])->default('TIEN_MAT');
            $table->enum('trangthai', ['CHO_THANH_TOAN', 'DA_THANH_TOAN', 'DA_HUY'])->default('DA_THANH_TOAN');
            $table->timestamp('ngaytao')->useCurrent();
        });

        Schema::create('phieu_kham', function (Blueprint $table) {
            $table->id('record_id');
            $table->foreignId('pet_id')->constrained('thu_cung', 'pet_id');
            $table->foreignId('doctor_id')->constrained('nguoi_dung', 'user_id');
            $table->timestamp('ngaykham')->useCurrent();
            $table->text('trieuchung')->nullable();
            $table->text('chandoan');
            $table->decimal('cannangkhi_kham', 5, 2);
            $table->string('huong_xuly', 20)->nullable();
            $table->boolean('yeu_cau_cach_ly')->default(false);
            $table->foreignId('invoice_id')->nullable()->constrained('hoa_don', 'invoice_id')->nullOnDelete();
            $table->foreignId('surgery_id')->nullable()->constrained('danh_muc_phau_thuat', 'surgery_id')->nullOnDelete();
            $table->unsignedBigInteger('surgery_price')->nullable();
        });

        Schema::create('don_thuoc', function (Blueprint $table) {
            $table->id('prescription_id');
            $table->foreignId('record_id')->constrained('phieu_kham', 'record_id');
            $table->foreignId('item_id')->constrained('kho_hang', 'item_id');
            $table->foreignId('invoice_id')->nullable()->constrained('hoa_don', 'invoice_id')->nullOnDelete();
            $table->unsignedInteger('soluong');
            $table->text('huongdan')->nullable();
            $table->timestamps();
        });

        Schema::create('chi_tiet_hoa_don', function (Blueprint $table) {
            $table->id('detail_id');
            $table->foreignId('invoice_id')->constrained('hoa_don', 'invoice_id')->cascadeOnDelete();
            $table->foreignId('prescription_id')->nullable()->unique()
                ->constrained('don_thuoc', 'prescription_id')->restrictOnDelete();
            $table->enum('loai_chiphi', ['THUOC', 'VAC_XIN', 'LUU_TRU', 'DICH_VU_SPA', 'PHI_KHAM', 'THUC_AN', 'DO_DUNG', 'PHAU_THUAT'])->default('THUOC');
            $table->string('ten_sanpham', 100);
            $table->string('donvitinh', 20);
            $table->unsignedInteger('soluong');
            $table->decimal('dongia', 12, 2);
            $table->decimal('thanhtien', 12, 2);
            $table->string('ghichu', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('luot_luu_tru', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_id')->constrained('thu_cung', 'pet_id')->cascadeOnDelete();
            $table->foreignId('cage_id')->constrained('long_nuoi', 'cage_id')->cascadeOnDelete();
            $table->dateTime('check_in');
            $table->dateTime('check_out')->nullable();
            $table->enum('status', ['checked_in', 'checked_out'])->default('checked_in');
            $table->foreignId('invoice_id')->nullable()->constrained('hoa_don', 'invoice_id')->nullOnDelete();
            $table->timestamps();
            $table->index(['pet_id', 'invoice_id']);
        });

        Schema::create('yeu_cau_luu_tru', function (Blueprint $table) {
            $table->id('request_id');
            $table->foreignId('pet_id')->constrained('thu_cung', 'pet_id')->restrictOnDelete();
            $table->foreignId('medical_record_id')->unique()->constrained('phieu_kham', 'record_id')->restrictOnDelete();
            $table->string('loai_long_yeu_cau', 20);
            $table->boolean('yeu_cau_cach_ly')->default(false);
            $table->string('ghi_chu', 255)->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignId('suggested_cage_id')->nullable()->constrained('long_nuoi', 'cage_id')->nullOnDelete();
            $table->foreignId('confirmed_by')->nullable()->constrained('nguoi_dung', 'user_id')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            $table->index(['pet_id', 'status']);
            $table->index(['status', 'created_at']);
        });

        Schema::create('hang_doi_kham', function (Blueprint $table) {
            $table->id('queue_id');
            $table->foreignId('pet_id')->constrained('thu_cung', 'pet_id');
            $table->foreignId('created_by')->nullable()->constrained('nguoi_dung', 'user_id')->nullOnDelete();
            $table->text('ghi_chu')->nullable();
            $table->text('ghi_chu_tiep_nhan')->nullable();
            $table->enum('trangthai', ['CHO_KHAM', 'DANG_KHAM', 'DA_KHAM'])->default('CHO_KHAM');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->index(['trangthai', 'created_at']);
        });

        Schema::create('luot_dich_vu_spa', function (Blueprint $table) {
            $table->id('spa_usage_id');
            $table->foreignId('pet_id')->constrained('thu_cung', 'pet_id')->restrictOnDelete();
            $table->foreignId('item_id')->constrained('kho_hang', 'item_id')->restrictOnDelete();
            $table->foreignId('staff_id')->constrained('nguoi_dung', 'user_id')->restrictOnDelete();
            $table->unsignedInteger('soluong');
            $table->string('ten_dichvu', 100);
            $table->string('donvitinh', 20);
            $table->decimal('dongia', 12, 2);
            $table->decimal('thanhtien', 12, 2);
            $table->string('ghichu', 255)->nullable();
            $table->foreignId('invoice_id')->nullable()->constrained('hoa_don', 'invoice_id')->nullOnDelete();
            $table->timestamps();
            $table->index(['pet_id', 'invoice_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('luot_dich_vu_spa');
        Schema::dropIfExists('hang_doi_kham');
        Schema::dropIfExists('yeu_cau_luu_tru');
        Schema::dropIfExists('luot_luu_tru');
        Schema::dropIfExists('chi_tiet_hoa_don');
        Schema::dropIfExists('don_thuoc');
        Schema::dropIfExists('phieu_kham');
        Schema::dropIfExists('hoa_don');
    }
};
