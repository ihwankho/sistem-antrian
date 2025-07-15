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
            $table->unsignedBigInteger('id_departemen')->nullable()->after('role'); //Menambah kolom id_departemen
            $table->integer('role')->change(); //mengubah tipe kolom
            $table->foreign('id_departemen')->references('id')->on('departemens')->onDelete('set null'); //Tambah kolom id_departemen
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['id_departemen']);
            $table->dropColumn('id_departemen');

            // Kembalikan tipe role ke string (varchar)
            $table->string('role')->change();
        });
    }
};
