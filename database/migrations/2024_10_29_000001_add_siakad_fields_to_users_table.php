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
            if (!Schema::hasColumn('users', 'nim')) {
                $table->string('nim')->nullable()->unique()->after('email'); // Nomor Induk Mahasiswa
            }
            if (!Schema::hasColumn('users', 'nip')) {
                $table->string('nip')->nullable()->unique()->after('nim'); // Nomor Induk Pegawai (Dosen)
            }
            if (!Schema::hasColumn('users', 'prodi')) {
                $table->string('prodi')->nullable()->after('nip'); // Program Studi
            }
            if (!Schema::hasColumn('users', 'is_sso')) {
                $table->boolean('is_sso')->default(false)->after('prodi'); // SSO user flag
            }
            if (!Schema::hasColumn('users', 'last_siakad_sync')) {
                $table->timestamp('last_siakad_sync')->nullable()->after('is_sso'); // Last sync from SIAKAD
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['nim', 'nip', 'prodi', 'is_sso', 'last_siakad_sync']);
        });
    }
};
