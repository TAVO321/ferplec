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
        Schema::create('valores_producto', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('campo_id')->constrained('campos_categoria')->cascadeOnDelete();
            $table->string('valor')->nullable();
            $table->timestamps();

            $table->unique(['producto_id', 'campo_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('valores_producto');
    }
};