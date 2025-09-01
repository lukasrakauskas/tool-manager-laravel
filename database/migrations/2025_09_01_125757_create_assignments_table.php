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
            $table->foreignId('tool_id')->constrained()->cascadeOnDelete();
            $table->foreignId('worker_id')->constrained()->cascadeOnDelete();
            $table->timestamp('assigned_at');
            $table->timestamp('due_at')->nullable();
            $table->timestamp('returned_at')->nullable();
            $table->string('status')->default('assigned')->index();
            $table->string('condition_out')->nullable();
            $table->string('condition_in')->nullable();
            $table->timestamps();
            $table->unique(['tool_id', 'status']);
            $table->index(['tool_id', 'worker_id']);
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
