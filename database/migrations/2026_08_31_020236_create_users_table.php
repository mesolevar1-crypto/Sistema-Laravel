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
        Schema::create('usuarios', function (Blueprint $table) {
    $table->id('id_usuario');
    $table->string('contraseña');
    
    $table->foreignId('id_persona')
          ->constrained('personas', 'id_persona')
          ->onDelete('cascade');

    $table->foreignId('id_rol')
          ->constrained('roles', 'id_rol')
          ->onDelete('cascade');

    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
