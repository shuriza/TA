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
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // Kode MK: e.g., TI101
            $table->string('name'); // Nama MK
            $table->foreignId('lecturer_id')->nullable()->constrained('users')->nullOnDelete(); // Dosen pengampu
            $table->string('semester'); // e.g., Ganjil 2024/2025
            $table->string('class')->nullable(); // e.g., A, B, C
            $table->text('description')->nullable();
            $table->string('color')->default('#3B82F6'); // Untuk UI
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('semester');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
