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
        Schema::table('invitations', function (Blueprint $table) {
            $table->enum('type', ['local', 'aai'])
                ->after('course_id')
                ->default('local');

            $table->dropUnique('invitations_invitation_token_unique');
            $table->string('invitation_token', 32)
                ->unique()
                ->nullable()
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropColumn('type');

            $table->dropUnique('invitations_invitation_token_unique');
            $table->string('invitation_token', 32)
                ->unique()
                ->change();
        });
    }
};
