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
        Schema::create('sales_details', function (Blueprint $table) {
            $table->id('id_detalle');
            $table->foreignId('id_venta')
          ->constrained('sales', 'id_venta')
          ->onDelete('cascade');
          $table->foreignId('id_producto')
          ->constrained('products', 'id_producto')
          ->onDelete('cascade');
          $table->integer('cantidad')->unsigned();
          $table->integer('precio_venta')->unsigned();
          $table->decimal('descuento_porcentaje', 10, 2);
          $table->decimal('descuento_valor', 10, 2);
          $table->decimal('subtotal', 10, 2);
          $table->foreignId('id_unidad')
          ->constrained('units', 'id_unidad')
          ->onDelete('cascade');
          $table->integer('cantidad_por_unidad')->unsigned();
          $table->foreignId('id_unidad_contenido')
          ->constrained('units', 'id_unidad')
          ->onDelete('cascade');
          $table->integer('costo_unitario')->unsigned();
          $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales_details');
    }
};
