<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('fcm_token')->nullable()->after('phone');
        });

        DB::statement("UPDATE users SET fcm_token = json_extract(fcm_tokens, '$[0]') WHERE fcm_tokens IS NOT NULL");

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('fcm_tokens');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('fcm_tokens')->nullable()->after('phone');
        });

        DB::statement('UPDATE users SET fcm_tokens = CASE WHEN fcm_token IS NOT NULL THEN json_array(fcm_token) ELSE NULL END');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('fcm_token');
        });
    }
};
