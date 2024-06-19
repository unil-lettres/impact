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
        Schema::table('cards', function (Blueprint $table) {
            $table->bigInteger('legacy_id')->unsigned()->nullable();
        });
        Schema::table('courses', function (Blueprint $table) {
            $table->bigInteger('legacy_id')->unsigned()->nullable();
        });
        Schema::table('folders', function (Blueprint $table) {
            $table->bigInteger('legacy_id')->unsigned()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cards', function (Blueprint $table) {
            $table->dropColumn('legacy_id');
        });
        Schema::table('folders', function (Blueprint $table) {
            $table->dropColumn('legacy_id');
        });
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('legacy_id');
        });
    }
};
