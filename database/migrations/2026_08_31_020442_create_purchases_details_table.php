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
        Schema::create('purchases_details', function (Blueprint $table) {
            $table->id('id_detalle');
            $table->foreignId('id_compra')
          ->constrained('purchases', 'id_compra')
          ->onDelete('cascade');
          $table->foreignId('id_producto')
          ->constrained('products', 'id_producto')
          ->onDelete('cascade');
          $table->integer('cantidad')->unsigned();
          $table->decimal('subtotal', 10, 2);
          $table->foreignId('id_unidad')
          ->constrained('units', 'id_unidad')
          ->onDelete('cascade');
          $table->integer('precio_compra')->unsigned();
          $table->integer('cantidad_por_unidad')->unsigned();
          $table->foreignId('id_unidad_contenido')
          ->constrained('units', 'id_unidad')
          ->onDelete('cascade');
          $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases_details');
    }
};
