<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lotes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('producto_id')->constrained('productos')->cascadeOnDelete();
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->nullOnDelete();
            $table->string('numero_lote')->nullable();
            $table->unsignedInteger('cantidad_inicial')->default(0);
            $table->unsignedInteger('cantidad_disponible')->default(0);
            $table->decimal('costo_unitario', 12, 2)->default(0);
            $table->date('fecha_ingreso');
            $table->text('nota')->nullable();
            $table->timestamps();

            $table->index(['producto_id', 'cantidad_disponible']);
            $table->index('fecha_ingreso');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lotes');
    }
};
