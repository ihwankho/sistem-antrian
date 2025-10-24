<?php

// Buka file migrasi yang baru dibuat (contoh: xxxx_xx_xx_xxxxxx_make_nik_nullable_in_pengunjungs_table.php)

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengunjungs', function (Blueprint $table) {
            // Baris ini akan mengubah kolom 'nik' menjadi boleh NULL
            $table->string('nik')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pengunjungs', function (Blueprint $table) {
            // Ini untuk mengembalikan jika diperlukan
            $table->string('nik')->nullable(false)->change();
        });
    }
};
