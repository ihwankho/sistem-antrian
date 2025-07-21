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
            // Hapus foreign key dan kolom
            $table->dropForeign(['id_departemen']);
            $table->dropColumn('id_departemen');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pengunjungs', function (Blueprint $table) {
            // Tambahkan kembali kolom dan foreign key
            $table->unsignedBigInteger('id_departemen')->after('foto_wajah');
            $table->foreign('id_departemen')->references('id')->on('departemens')->onDelete('cascade');
        });
    }
};
