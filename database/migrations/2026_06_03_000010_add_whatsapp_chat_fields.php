<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('reply_to_id')->nullable()->constrained('messages')->nullOnDelete()->after('type');
            $table->json('metadata')->nullable()->after('content');
            $table->enum('status', ['sent', 'delivered', 'read'])->default('sent')->after('read_at');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('last_seen_at')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropForeign(['reply_to_id']);
            $table->dropColumn(['reply_to_id', 'metadata', 'status']);
        });
    }
};
