<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('states', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('course_id')->unsigned();
            $table->foreign('course_id')
                ->references('id')
                ->on('courses')
                ->onDelete('cascade');
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->mediumInteger('position')->nullable();
            $table->json('permissions');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::table('cards', function (Blueprint $table) {
            $table->bigInteger('state_id')->unsigned()
                ->nullable()
                ->after('file_id');
            $table->foreign('state_id')
                ->references('id')
                ->on('states')
                ->onDelete('set null');
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
            $table->dropColumn('state_id');
        });

        Schema::dropIfExists('states');
    }
}
