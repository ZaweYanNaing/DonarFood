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
        Schema::create('donations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('donation_issue_id')->constrained()->cascadeOnDelete();
            $table->string('amount')->nullable(); // e.g., "5 kg rice", "20 bread loaves"
            $table->text('items_description')->nullable();
            $table->enum('status', ['pledged', 'confirmed', 'delivered', 'cancelled'])->default('pledged');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['donation_issue_id', 'status']);
            $table->index(['user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('donations');
    }
};
