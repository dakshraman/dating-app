<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('state')->nullable()->after('longitude');
            $table->string('city')->nullable()->after('state');
            $table->string('religion')->nullable()->after('city');
            $table->string('mother_tongue')->nullable()->after('religion');
            $table->string('dietary_preference')->nullable()->after('mother_tongue');
            $table->string('education')->nullable()->after('dietary_preference');
            $table->string('profession')->nullable()->after('education');
            $table->string('income_range')->nullable()->after('profession');
        });

        Schema::table('user_preferences', function (Blueprint $table) {
            $table->string('religion_preference')->nullable()->after('max_distance');
            $table->string('mother_tongue_preference')->nullable()->after('religion_preference');
            $table->string('dietary_preference')->nullable()->after('mother_tongue_preference');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['state', 'city', 'religion', 'mother_tongue', 'dietary_preference', 'education', 'profession', 'income_range']);
        });

        Schema::table('user_preferences', function (Blueprint $table) {
            $table->dropColumn(['religion_preference', 'mother_tongue_preference', 'dietary_preference']);
        });
    }
};
