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
        Schema::create('apartados', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->string('nombre_cliente');
            $table->string('telefono');
            $table->string('direccion')->nullable();
            $table->string('ubicacion')->nullable();
            $table->text('nota')->nullable();
            $table->string('estado')->default('apartado');
            $table->decimal('subtotal', 12, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('apartados');
    }
};
