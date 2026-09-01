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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id('id_factura');
            $table->string('numero_factura');
            $table->foreignId('id_venta')
          ->constrained('sales', 'id_venta')
          ->onDelete('cascade');
          $table->date('fecha_registro');
          $table->date('fecha_emision');
          $table->decimal('subtotal', 10, 2);
          $table->decimal('descuento_valor', 10, 2);
          $table->decimal('total', 10, 2);
          $table->tinyInteger('estado')->default(1);
          $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
