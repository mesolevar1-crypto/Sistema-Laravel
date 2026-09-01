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
        Schema::create('sales', function (Blueprint $table) {
            $table->id('id_venta');
            $table->foreignId('id_cliente')
          ->constrained('customers', 'id_cliente')
          ->onDelete('cascade');
          $table->foreignId('id_usuario')
          ->constrained('users', 'id_usuario')
          ->onDelete('cascade');
            $table->date('fecha');
            $table->decimal('total', 10, 2);
            $table->string('metodo_pago');
            $table->tinyInteger('estado')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
