<?php

use Illuminate\\Database\\Migrations\\Migration;
use Illuminate\\Database\\Schema\\Blueprint;
use Illuminate\\Support\\Facades\\DB;
use Illuminate\\Support\\Facades\\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lich_su_can_nang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pet_id')->constrained('thu_cung', 'pet_id')->cascadeOnDelete();
            $table->decimal('cannang', 5, 2);
            $table->timestamp('ngaydo')->useCurrent();
            $table->timestamps();
            $table->index(['pet_id', 'ngaydo']);
        });

        // Backfill cân nặng hiện tại của các hồ sơ đã tồn tại trước khi có bảng lịch sử.
        $pets = DB::table('thu_cung')
            ->whereNotNull('cannang')
            ->get(['pet_id', 'cannang', 'ngaytao']);

        foreach ($pets as $pet) {
            DB::table('lich_su_can_nang')->insert([
                'pet_id' => $pet->pet_id,
                'cannang' => $pet->cannang,
                'ngaydo' => $pet->ngaytao ?? now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('lich_su_can_nang');
    }
};
