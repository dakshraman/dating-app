<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('swipes', function (Blueprint $table) {
            $table->boolean('is_super_like')->default(false)->after('direction');
        });
    }

    public function down(): void
    {
        Schema::table('swipes', function (Blueprint $table) {
            $table->dropColumn('is_super_like');
        });
    }
};
