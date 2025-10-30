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
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('due_at')->nullable();
            $table->enum('status', ['draft', 'published', 'closed'])->default('published');
            $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
            $table->integer('effort_mins')->nullable(); // Estimasi effort dalam menit
            $table->integer('impact')->nullable(); // 0-100 skala dampak
            $table->string('tag')->nullable(); // e.g., kuliah, lab, project
            $table->string('lms_url')->nullable(); // Deep link ke LMS eksternal
            $table->boolean('allow_late_submission')->default(false);
            $table->integer('max_score')->default(100);
            $table->json('attachments')->nullable(); // Array file attachments
            $table->timestamps();
            $table->softDeletes();
            
            $table->index('due_at');
            $table->index('status');
            $table->index(['course_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
