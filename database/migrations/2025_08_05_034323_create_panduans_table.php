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
        Schema::create('panduans', function (Blueprint $table) {
            $table->id();
            $table->text('isi_panduan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('panduans');
    }
};
