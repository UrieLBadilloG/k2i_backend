<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('telefono', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')->constrained('persona')->onDelete('cascade');
            $table->string('telefono');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telefono');
    }
};
