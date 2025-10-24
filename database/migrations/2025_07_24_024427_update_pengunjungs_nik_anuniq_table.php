<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('pengunjungs', function (Blueprint $table) {
            // [TETAP] Baris ini menghapus aturan unik dari kolom 'nik'.
            $table->dropUnique(['nik']);
            
            // [BARU] Baris ini menambahkan aturan unik ke kolom 'no_hp'.
            $table->unique('no_hp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengunjungs', function (Blueprint $table) {
            // [TETAP] Mengembalikan aturan unik ke 'nik' jika migrasi dibatalkan.
            $table->unique('nik');
            
            // [BARU] Menghapus aturan unik dari 'no_hp' jika migrasi dibatalkan.
            $table->dropUnique(['no_hp']);
        });
    }
};