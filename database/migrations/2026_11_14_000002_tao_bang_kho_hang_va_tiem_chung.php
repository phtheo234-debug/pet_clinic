<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kho_hang', function (Blueprint $table) {
            $table->id('item_id');
            $table->string('masanpham', 20)->unique();
            $table->string('ten_sanpham', 100);
            $table->enum('danhmuc', ['THUOC', 'VAC_XIN', 'THUC_AN', 'DO_DUNG', 'DICH_VU_SPA']);
            $table->string('donvitinh', 20);
            $table->decimal('dongia', 12, 2);
            $table->unsignedInteger('soluongton')->default(0);
        });

        Schema::create('lo_hang', function (Blueprint $table) {
            $table->id('batch_id');
            $table->foreignId('item_id')->constrained('kho_hang', 'item_id')->cascadeOnDelete();
            $table->string('solo', 50);
            $table->unsignedInteger('soluong')->default(0);
            $table->date('ngaynhap');
            $table->date('ngaysanxuat')->nullable();
            $table->date('ngayhethan')->nullable();
            $table->enum('trangthai', ['DANG_SU_DUNG', 'HET_HAN', 'DA_TIEU_HUY'])->default('DANG_SU_DUNG');
            $table->unique(['item_id', 'solo'], 'lo_hang_mat_hang_ma_lo_unique');
        });

        Schema::create('danh_muc_phau_thuat', function (Blueprint $table) {
            $table->id('surgery_id');
            $table->string('ma_phauthuat', 30)->unique();
            $table->string('ten_phauthuat', 100);
            $table->unsignedBigInteger('gia');
            $table->boolean('trangthai')->default(true);
            $table->timestamps();
        });

        Schema::create('loai_vac_xin', function (Blueprint $table) {
            $table->id('vaccination_type_id');
            $table->string('name', 100)->unique();
            $table->boolean('is_active')->default(true);
            $table->foreignId('inventory_item_id')->nullable()
                ->constrained('kho_hang', 'item_id')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('lich_su_tiem_chung', function (Blueprint $table) {
            $table->id('vaccination_id');
            $table->foreignId('pet_id')->constrained('thu_cung', 'pet_id')->cascadeOnDelete();
            $table->foreignId('vaccination_type_id')->constrained('loai_vac_xin', 'vaccination_type_id');
            $table->date('vaccinated_at');
            $table->date('next_due_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('administered_by')->nullable()->constrained('nguoi_dung', 'user_id')->nullOnDelete();
            $table->timestamps();
            $table->index(['pet_id', 'vaccinated_at']);
            $table->index('next_due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lich_su_tiem_chung');
        Schema::dropIfExists('loai_vac_xin');
        Schema::dropIfExists('danh_muc_phau_thuat');
        Schema::dropIfExists('lo_hang');
        Schema::dropIfExists('kho_hang');
    }
};
