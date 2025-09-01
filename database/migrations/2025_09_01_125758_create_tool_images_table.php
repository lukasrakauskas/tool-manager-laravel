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
        Schema::create('tool_images', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tool_id')->constrained()->cascadeOnDelete();
            $table->string('path');
            $table->string('visibility')->default('private');
            $table->timestamps();
            $table->index('tool_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tool_images');
    }
};
