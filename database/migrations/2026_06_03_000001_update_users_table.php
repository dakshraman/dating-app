<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('gender')->nullable()->after('email');
            $table->date('birth_date')->nullable()->after('gender');
            $table->text('bio')->nullable()->after('birth_date');
            $table->string('location')->nullable()->after('bio');
            $table->decimal('latitude', 10, 7)->nullable()->after('location');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            $table->string('profile_photo')->nullable()->after('longitude');
            $table->boolean('is_verified')->default(false)->after('profile_photo');
            $table->boolean('is_active')->default(true)->after('is_verified');
            $table->timestamp('last_active_at')->nullable()->after('is_active');
            $table->timestamp('last_seen_at')->nullable()->after('last_active_at');
            $table->string('phone')->nullable()->unique()->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['gender', 'birth_date', 'bio', 'location', 'latitude', 'longitude', 'profile_photo', 'is_verified', 'is_active', 'last_active_at', 'last_seen_at', 'phone']);
        });
    }
};
