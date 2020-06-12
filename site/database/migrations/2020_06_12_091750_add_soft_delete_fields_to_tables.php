<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSoftDeleteFieldsToTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('folders', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('files', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('invitations', function (Blueprint $table) {
            $table->softDeletes();
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('folders', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('invitations', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });

        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }
}
