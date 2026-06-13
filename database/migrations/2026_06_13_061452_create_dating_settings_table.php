<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dating_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('daily_swipe_limit')->default(10);
            $table->integer('daily_super_like_limit')->default(3);
            $table->integer('boost_duration_minutes')->default(30);
            $table->boolean('verification_required_for_swiping')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dating_settings');
    }
};
