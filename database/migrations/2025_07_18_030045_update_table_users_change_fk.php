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
        Schema::table('users', function (Blueprint $table) {
            // Hapus foreign key dan kolom id_departemen
            $table->dropForeign(['id_departemen']);
            $table->dropColumn('id_departemen');

            // Tambah kolom id_loket dan set foreign key ke tabel lokets
            $table->unsignedBigInteger('id_loket')->nullable()->after('role');
            $table->foreign('id_loket')->references('id')->on('lokets')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hapus foreign key dan kolom id_loket
            $table->dropForeign(['id_loket']);
            $table->dropColumn('id_loket');

            // Tambah kembali kolom id_departemen dan set foreign key ke tabel departemens
            $table->unsignedBigInteger('id_departemen')->nullable()->after('role');
            $table->foreign('id_departemen')->references('id')->on('departemens')->onDelete('set null');
        });
    }
};
