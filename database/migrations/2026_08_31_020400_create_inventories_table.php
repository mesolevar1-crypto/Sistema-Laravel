<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * La tabla invoices tiene una columna 'fecha_registro' que no
     * pertenece a la estructura original de 'factura' (legacy).
     * Probablemente quedó copiada por error de otra migración
     * (como customers, que sí usa fecha_registro). El modelo Venta
     * nunca la usa, así que se elimina.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn('fecha_registro');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->date('fecha_registro')->nullable();
        });
    }
};