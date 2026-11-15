<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hang_doi_kham', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable()->change();
        });

        Schema::create('lich_hen_khach', function (Blueprint $table) {
            $table->id();
            $table->string('ma_lich_hen', 30)->unique();
            $table->foreignId('owner_id')->constrained('chu_nuoi','owner_id')->cascadeOnDelete();
            $table->foreignId('pet_id')->constrained('thu_cung','pet_id')->cascadeOnDelete();
            $table->foreignId('queue_id')->nullable()->constrained('hang_doi_kham','queue_id')->nullOnDelete();
            $table->enum('loai_dich_vu',['MEDICAL','SPA','BOTH']);
            $table->date('ngay_hen');
            $table->enum('ca_hen',['SANG','CHIEU']);
            $table->time('gio_mong_muon');
            $table->text('ghi_chu_kham')->nullable();
            $table->json('dich_vu_spa')->nullable();
            $table->enum('trang_thai',['CHO_XAC_NHAN','DA_XAC_NHAN','DA_HOAN_THANH','DA_HUY'])->default('CHO_XAC_NHAN');
            $table->timestamps();
            $table->index(['ngay_hen','ca_hen','trang_thai']);
            $table->index(['owner_id','pet_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lich_hen_khach');
        DB::table('hang_doi_kham')->whereNull('created_by')->delete();
        Schema::table('hang_doi_kham', function (Blueprint $table) {
            $table->unsignedBigInteger('created_by')->nullable(false)->change();
        });
    }
};
