<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::dropIfExists('invitations');

        Schema::create('invitations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('email')->unique();
            $table->string('invitation_token', 32)->unique();
            $table->bigInteger('creator_id')->unsigned();
            $table->foreign('creator_id')->references('id')->on('users');
            $table->bigInteger('course_id')->unsigned();
            $table->foreign('course_id')->references('id')->on('courses');
            $table->timestamp('registered_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invitations');

        Schema::create('invitations', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('email')->unique();
            $table->string('invitation_token', 32)->unique()->nullable();
            $table->bigInteger('creator_id')->unsigned()->nullable();
            $table->foreign('creator_id')->references('id')->on('users');
            $table->timestamp('registered_at')->nullable();
            $table->timestamps();
        });
    }
};
