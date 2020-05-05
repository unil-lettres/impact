<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255);
            $table->string('filename', 100)
                ->nullable();
            $table->enum('status', ['processing', 'failed', 'ready'])
                ->default('processing');
            $table->enum('type', ['other', 'document', 'image', 'video', 'audio'])
                ->default('other');
            $table->bigInteger('size')
                ->nullable();
            $table->mediumInteger('width')
                ->nullable();
            $table->mediumInteger('height')
                ->nullable();
            $table->integer('length')
                ->nullable();
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
        Schema::dropIfExists('files');
    }
}
