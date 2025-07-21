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
        Schema::table('departemens', function (Blueprint $table) {
            $table->unsignedBigInteger('id_loket')->after('nama_departemen');
            $table->foreign('id_loket')->references('id')->on('lokets')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('departemens', function (Blueprint $table) {
            $table->dropForeign(['id_loket']);
            $table->dropColumn('id_loket');
        });
    }
};
