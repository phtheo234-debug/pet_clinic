<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hang_doi_kham', function (Blueprint $table) {
            $table->text('ghi_chu_tiep_nhan')->nullable()->after('ghi_chu');
        });
    }

    public function down(): void
    {
        Schema::table('hang_doi_kham', function (Blueprint $table) {
            $table->dropColumn('ghi_chu_tiep_nhan');
        });
    }
};
