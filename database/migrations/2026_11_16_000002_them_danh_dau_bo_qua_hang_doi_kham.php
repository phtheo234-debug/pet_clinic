<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hang_doi_kham', function (Blueprint $table) {
            $table->timestamp('bo_qua_luc')->nullable()->after('trangthai');
            $table->index(['trangthai', 'bo_qua_luc']);
        });
    }

    public function down(): void
    {
        Schema::table('hang_doi_kham', function (Blueprint $table) {
            $table->dropIndex(['trangthai', 'bo_qua_luc']);
            $table->dropColumn('bo_qua_luc');
        });
    }
};
