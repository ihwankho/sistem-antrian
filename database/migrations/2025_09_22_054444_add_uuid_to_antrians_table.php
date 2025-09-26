<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('antrians', function (Blueprint $table) {
            // Tambahkan kolom uuid setelah kolom 'id'
            // Pastikan unik dan ada index untuk pencarian cepat
            $table->uuid('uuid')->after('id')->unique()->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('antrians', function (Blueprint $table) {
            $table->dropColumn('uuid');
        });
    }
};