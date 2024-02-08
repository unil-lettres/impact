<?php

use App\Enrollment;
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
        // As we can't update enum values easly (https://github.com/laravel/framework/issues/35096)
        // we will create a new column, fill it with the new values and then remove the old column.

        Schema::table('enrollments', function (Blueprint $table) {
            $table->enum('tmp_role', ['member', 'manager'])->default('member')->after('role');
        });

        Enrollment::where('role', 'teacher')->update(['tmp_role' => 'manager']);
        Enrollment::where('role', 'student')->update(['tmp_role' => 'member']);

        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        // Can't rename enum column: https://laravel.com/docs/8.x/migrations#renaming-columns
        DB::statement('ALTER TABLE enrollments CHANGE COLUMN tmp_role role ENUM("member", "manager") NOT NULL DEFAULT "member"');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table) {
            $table->enum('tmp_role', ['student', 'teacher'])->default('student')->after('role');
        });

        Enrollment::where('role', 'manager')->update(['tmp_role' => 'teacher']);
        Enrollment::where('role', 'member')->update(['tmp_role' => 'student']);

        Schema::table('enrollments', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        DB::statement('ALTER TABLE enrollments CHANGE COLUMN tmp_role role ENUM("member", "manager") NOT NULL DEFAULT "member"');
    }
};
