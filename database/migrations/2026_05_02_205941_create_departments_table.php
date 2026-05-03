<?php
// database/migrations/2024_01_01_000001_create_departments_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('code', 10)->unique();
            $table->text('description')->nullable();
            $table->foreignId('manager_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('parent_id')->nullable()->constrained('departments')->onDelete('set null');
            $table->decimal('budget', 15, 2)->nullable()->default(0);
            $table->string('email', 100)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('location', 200)->nullable();
            $table->integer('capacity')->nullable()->default(0);
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('code');
            $table->index('is_active');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
