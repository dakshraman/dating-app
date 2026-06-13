<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('daily_swipe_usage', function (Blueprint $table) {
            $table->integer('super_like_count')->default(0)->after('count');
        });
    }

    public function down(): void
    {
        Schema::table('daily_swipe_usage', function (Blueprint $table) {
            $table->dropColumn('super_like_count');
        });
    }
};
