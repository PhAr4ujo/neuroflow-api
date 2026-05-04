<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('profile_id')
                ->nullable()
                ->after('id')
                ->constrained();
        });

        $userProfileId = DB::table('profiles')
            ->where('slug', 'user')
            ->value('id');

        if ($userProfileId !== null) {
            DB::table('users')
                ->whereNull('profile_id')
                ->update(['profile_id' => $userProfileId]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('profile_id');
        });
    }
};
