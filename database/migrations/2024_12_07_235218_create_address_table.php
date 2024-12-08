<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('direccion', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')->constrained('persona')->onDelete('cascade');
            $table->string('calle');
            $table->string('numero_exterior');
            $table->string('numero_interior')->nullable();
            $table->string('colonia');
            $table->string('cp');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('direccion');
    }
};
