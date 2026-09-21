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
        Schema::create('detalle_apartados', function (Blueprint $table) {
            $table->id();
            $table->foreignId('apartado_id')->constrained('apartados')->cascadeOnDelete();
            $table->foreignId('producto_id')->nullable()->constrained('productos')->nullOnDelete();
            $table->string('nombre_producto');
            $table->decimal('precio', 12, 2);
            $table->unsignedInteger('cantidad')->default(1);
            $table->decimal('subtotal', 12, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('detalle_apartados');
    }
};