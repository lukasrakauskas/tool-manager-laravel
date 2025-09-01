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
        Schema::create('tools', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('category')->nullable()->index();
            $table->string('serial')->unique();
            $table->string('status')->default('available')->index();
            $table->unsignedInteger('power_watts')->nullable();
            $table->string('size')->nullable();
            $table->json('attributes')->nullable();
            $table->string('qr_secret')->nullable()->unique();
            $table->timestamps();
            $table->index('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tools');
    }
};
