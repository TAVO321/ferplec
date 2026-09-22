<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pedidos_especiales', function (Blueprint $table) {
            $table->id();
            $table->string('codigo')->unique();
            $table->foreignId('cliente_id')->nullable()->constrained('clientes')->nullOnDelete();
            $table->string('nombre_cliente');
            $table->string('telefono');
            $table->string('direccion')->nullable();
            $table->string('ubicacion')->nullable();
            $table->string('producto_solicitado');
            $table->unsignedInteger('cantidad')->default(1);
            $table->string('unidad_de_medida')->default('und');
            $table->date('fecha_requerida')->nullable();
            $table->string('estado')->default('solicitado');
            $table->decimal('subtotal', 12, 2)->nullable();
            $table->foreignId('proveedor_id')->nullable()->constrained('proveedores')->nullOnDelete();
            $table->text('nota')->nullable();
            $table->timestamps();

            $table->index('estado');
            $table->index('telefono');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pedidos_especiales');
    }
};
