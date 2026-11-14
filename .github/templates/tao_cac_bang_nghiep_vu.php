<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owners', function (Blueprint $table) {
            $table->id('owner_id');
            $table->string('ho_ten', 100);
            $table->string('phone', 20)->unique();
            $table->string('diachi', 255)->nullable();
            $table->string('email', 100)->nullable();
            $table->timestamp('ngaytao')->useCurrent();
        });

        Schema::create('cages', function (Blueprint $table) {
            $table->id('cage_id');
            $table->string('malong', 20)->unique();
            $table->string('ten_long', 50);
            $table->enum('loailong', ['THUONG', 'CAP_CUU_ICU', 'CACH_LY'])->default('THUONG');
            $table->integer('succhua')->default(1);
            $table->integer('soluonghientai')->default(0);
            $table->decimal('tai_trong_toi_da', 7, 2)->nullable();
            $table->string('loai_ap_dung', 30)->nullable();
            $table->enum('trangthai', ['CON_TRONG', 'DA_CO_THU', 'DANG_VE_SINH', 'BAO_TRI'])->default('CON_TRONG');
        });

        Schema::create('pets', function (Blueprint $table) {
            $table->id('pet_id');
            $table->string('mathucung', 20)->unique();
            $table->foreignId('owner_id')->constrained('owners', 'owner_id')->cascadeOnDelete();
            $table->foreignId('cage_id')->nullable()->constrained('cages', 'cage_id')->nullOnDelete();
            $table->string('ten_thucung', 50);
            $table->string('loai', 30);
            $table->string('giong', 50)->nullable();
            $table->enum('gioitinh', ['DUC', 'CAI', 'CHUA_XAC_DINH'])->default('CHUA_XAC_DINH');
            $table->date('ngaysinh')->nullable();
            // Khong tao machip/qr_code: ma chip da bi loai khoi thiet ke hien tai.
            $table->enum('trangthai', ['NOI_TRU', 'NGOAI_TRU', 'DA_XUAT_VIEN', 'CHUYEN_VIEN'])->default('NGOAI_TRU');
            $table->timestamp('ngaytao')->useCurrent();
        });

        Schema::create('surgeries', function (Blueprint $table) {
            $table->id('surgery_id');
            $table->string('ma_phauthuat', 30)->unique();
            $table->string('ten_phauthuat', 100);
            $table->unsignedBigInteger('gia');
            $table->boolean('trangthai')->default(true);
            $table->timestamps();
        });

        Schema::create('weight_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_id')->constrained('pets', 'pet_id')->cascadeOnDelete();
            $table->decimal('cannang', 5, 2);
            $table->timestamp('ngaydo')->useCurrent();
            $table->foreignId('nguoido')->constrained('users', 'user_id');
            $table->string('ghichu', 255)->nullable();
        });

        Schema::create('invoices', function (Blueprint $table) {
            $table->id('invoice_id');
            $table->string('mahoadon', 20)->unique();
            $table->foreignId('pet_id')->constrained('pets', 'pet_id');
            $table->foreignId('owner_id')->constrained('owners', 'owner_id');
            $table->decimal('tongtien', 12, 2);
            $table->enum('phuongthucthanhtoan', ['TIEN_MAT', 'CHUYEN_KHOAN', 'THE'])->default('TIEN_MAT');
            $table->enum('trangthai', ['CHO_THANH_TOAN', 'DA_THANH_TOAN', 'DA_HUY'])->default('DA_THANH_TOAN');
            $table->timestamp('ngaytao')->useCurrent();
        });

        Schema::create('pet_boardings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_id')->constrained('pets', 'pet_id')->cascadeOnDelete();
            $table->foreignId('cage_id')->constrained('cages', 'cage_id')->cascadeOnDelete();
            $table->dateTime('check_in');
            $table->dateTime('check_out')->nullable();
            $table->enum('status', ['checked_in', 'checked_out'])->default('checked_in');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices', 'invoice_id')->nullOnDelete();
            $table->timestamps();
            $table->index(['pet_id', 'invoice_id']);
        });

        Schema::create('inventory', function (Blueprint $table) {
            $table->id('item_id');
            $table->string('masanpham', 20)->unique();
            $table->string('ten_sanpham', 100);
            $table->enum('danhmuc', ['THUOC', 'VAC_XIN', 'THUC_AN', 'DO_DUNG', 'DICH_VU_SPA']);
            $table->string('donvitinh', 20)->default('');
            $table->decimal('dongia', 12, 2);
            $table->decimal('lieu_mg_kg', 10, 2)->nullable();
            $table->decimal('mg_moi_don_vi', 10, 2)->nullable();
            $table->unsignedInteger('soluongton')->default(0);
            $table->integer('tonkhotoithieu')->default(10);
        });

        Schema::create('inventory_batches', function (Blueprint $table) {
            $table->id('batch_id');
            $table->foreignId('item_id')->constrained('inventory', 'item_id')->cascadeOnDelete();
            $table->string('solo', 50);
            $table->integer('soluong')->default(0);
            $table->date('ngaynhap');
            $table->date('ngaysanxuat')->nullable();
            $table->date('ngayhethan')->nullable();
            $table->enum('trangthai', ['DANG_SU_DUNG', 'HET_HAN', 'DA_TIEU_HUY'])->default('DANG_SU_DUNG');
            $table->unique(['item_id', 'solo'], 'inventory_batches_item_solo_unique');
        });

        Schema::create('medical_records', function (Blueprint $table) {
            $table->id('record_id');
            $table->foreignId('pet_id')->constrained('pets', 'pet_id');
            $table->foreignId('doctor_id')->constrained('users', 'user_id');
            $table->timestamp('ngaykham')->useCurrent();
            $table->text('trieuchung')->nullable();
            $table->text('chandoan');
            $table->decimal('cannangkhi_kham', 5, 2);
            $table->enum('trangthai', ['CHUA_KHAM', 'DANG_DIEU_TRI', 'HOAN_THANH', 'CHUYEN_VIEN'])->default('CHUA_KHAM');
            $table->string('huong_xuly', 20)->nullable();
            $table->boolean('yeu_cau_cach_ly')->default(false);
            $table->foreignId('surgery_id')->nullable()->constrained('surgeries', 'surgery_id')->nullOnDelete();
            $table->unsignedBigInteger('surgery_price')->nullable();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices', 'invoice_id')->nullOnDelete();
        });

        Schema::create('prescriptions', function (Blueprint $table) {
            $table->id('prescription_id');
            $table->foreignId('record_id')->constrained('medical_records', 'record_id');
            $table->foreignId('item_id')->constrained('inventory', 'item_id');
            $table->foreignId('invoice_id')->nullable()->constrained('invoices', 'invoice_id')->nullOnDelete();
            $table->unsignedInteger('soluong');
            $table->text('huongdan')->nullable();
            $table->timestamps();
        });

        Schema::create('invoice_details', function (Blueprint $table) {
            $table->id('detail_id');
            $table->foreignId('invoice_id')->constrained('invoices', 'invoice_id')->cascadeOnDelete();
            $table->foreignId('prescription_id')->nullable()->unique()->constrained('prescriptions', 'prescription_id')->restrictOnDelete();
            $table->enum('loai_chiphi', ['THUOC', 'VAC_XIN', 'LUU_TRU', 'DICH_VU_SPA', 'PHI_KHAM', 'THUC_AN', 'DO_DUNG', 'PHAU_THUAT'])->default('THUOC');
            $table->string('ten_sanpham', 100);
            $table->string('donvitinh', 20);
            $table->unsignedInteger('soluong');
            $table->decimal('dongia', 12, 2);
            $table->decimal('thanhtien', 12, 2);
            $table->string('ghichu', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('spa_usages', function (Blueprint $table) {
            $table->id('spa_usage_id');
            $table->foreignId('pet_id')->constrained('pets', 'pet_id')->restrictOnDelete();
            $table->foreignId('item_id')->constrained('inventory', 'item_id')->restrictOnDelete();
            $table->foreignId('staff_id')->constrained('users', 'user_id')->restrictOnDelete();
            $table->unsignedInteger('soluong');
            $table->string('ten_dichvu', 100);
            $table->string('donvitinh', 20);
            $table->decimal('dongia', 12, 2);
            $table->decimal('thanhtien', 12, 2);
            $table->string('ghichu', 255)->nullable();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices', 'invoice_id')->nullOnDelete();
            $table->timestamps();
            $table->index(['pet_id', 'invoice_id']);
        });

        Schema::create('boarding_requests', function (Blueprint $table) {
            $table->id('request_id');
            $table->foreignId('pet_id')->constrained('pets', 'pet_id')->restrictOnDelete();
            $table->foreignId('medical_record_id')->unique()->constrained('medical_records', 'record_id')->restrictOnDelete();
            $table->string('loai_long_yeu_cau', 20);
            $table->boolean('yeu_cau_cach_ly')->default(false);
            $table->string('ghi_chu', 255)->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignId('suggested_cage_id')->nullable()->constrained('cages', 'cage_id')->nullOnDelete();
            $table->foreignId('confirmed_by')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
            $table->index(['pet_id', 'status']);
            $table->index(['status', 'created_at']);
        });

        Schema::create('medical_queue', function (Blueprint $table) {
            $table->id('queue_id');
            $table->foreignId('pet_id')->constrained('pets', 'pet_id');
            $table->foreignId('created_by')->constrained('users', 'user_id');
            $table->text('ghi_chu')->nullable();
            $table->enum('trangthai', ['CHO_KHAM', 'DANG_KHAM', 'DA_KHAM'])->default('CHO_KHAM');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->nullable();
            $table->index(['trangthai', 'created_at']);
        });

        Schema::create('vaccination_types', function (Blueprint $table) {
            $table->id('vaccination_type_id');
            $table->string('name', 100)->unique();
            $table->boolean('is_active')->default(true);
            $table->foreignId('inventory_item_id')->nullable()->constrained('inventory', 'item_id')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('pet_vaccinations', function (Blueprint $table) {
            $table->id('vaccination_id');
            $table->foreignId('pet_id')->constrained('pets', 'pet_id')->cascadeOnDelete();
            $table->foreignId('vaccination_type_id')->constrained('vaccination_types', 'vaccination_type_id');
            $table->date('vaccinated_at');
            $table->date('next_due_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('administered_by')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->timestamps();
            $table->index(['pet_id', 'vaccinated_at']);
            $table->index('next_due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pet_vaccinations');
        Schema::dropIfExists('vaccination_types');
        Schema::dropIfExists('medical_queue');
        Schema::dropIfExists('boarding_requests');
        Schema::dropIfExists('spa_usages');
        Schema::dropIfExists('invoice_details');
        Schema::dropIfExists('prescriptions');
        Schema::dropIfExists('medical_records');
        Schema::dropIfExists('inventory_batches');
        Schema::dropIfExists('inventory');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('weight_logs');
        Schema::dropIfExists('pet_boardings');
        Schema::dropIfExists('surgeries');
        Schema::dropIfExists('pets');
        Schema::dropIfExists('cages');
        Schema::dropIfExists('owners');
    }
};
