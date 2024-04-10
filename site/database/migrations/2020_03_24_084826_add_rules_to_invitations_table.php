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
            $table->dropForeign('invitations_course_id_foreign');
            $table->foreign('course_id')
                ->references('id')
                ->on('courses')
                ->onDelete('cascade');

            $table->dropForeign('invitations_creator_id_foreign');
            $table->foreign('creator_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invitations', function (Blueprint $table) {
            $table->dropForeign('invitations_course_id_foreign');
            $table->foreign('course_id')
                ->references('id')
                ->on('courses');

            $table->dropForeign('invitations_creator_id_foreign');
            $table->foreign('creator_id')
                ->references('id')
                ->on('users');
        });
    }
};
