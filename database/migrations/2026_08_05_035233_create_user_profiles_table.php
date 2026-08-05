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
        Schema::create('user_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('bio')->nullable();
            $table->string('phone')->nullable();
            $table->string('city')->nullable();
            $table->string('country')->nullable();
            $table->date('birth_date')->nullable();
            $table->string('website')->nullable();
            $table->string('facebook_url')->nullable();
            $table->string('twitter_url')->nullable();
            $table->string('instagram_url')->nullable();
            $table->boolean('is_public')->default(true);
            $table->timestamps();
        });

        // Add profile_photo_path to users table
        Schema::table('users', function (Blueprint $table) {
            $table->string('profile_photo_path')->nullable()->after('remember_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('profile_photo_path');
        });
        Schema::dropIfExists('user_profiles');
    }
};
