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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('mahasiswa')->after('email'); // mahasiswa, dosen, admin
            $table->string('nim')->nullable()->after('role');
            $table->string('nip')->nullable()->after('nim');
            $table->string('timezone')->default('Asia/Jakarta')->after('nip');
            $table->string('notify_channel')->default('database')->after('timezone'); // database, telegram, email, whatsapp
            $table->string('telegram_chat_id')->nullable()->after('notify_channel');
            $table->text('provider_tokens')->nullable()->after('telegram_chat_id'); // encrypted JSON for OAuth tokens
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'role',
                'nim',
                'nip',
                'timezone',
                'notify_channel',
                'telegram_chat_id',
                'provider_tokens'
            ]);
        });
    }
};
