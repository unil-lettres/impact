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
        Schema::create('files', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 255);
            $table->string('filename', 100)
                ->nullable();
            $table->enum('status', ['processing', 'transcoding', 'failed', 'ready'])
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
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
