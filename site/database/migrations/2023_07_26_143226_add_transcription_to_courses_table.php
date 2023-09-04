<?php

use App\Card;
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
        Schema::table('courses', function (Blueprint $table) {
            $table->enum('transcription', ['icor', 'text'])->default('icor')->after('external_id');
        });

        // !! Reset the box2 with an updated default value for all cards
        Card::query()->update(['box2' => json_decode(Card::TRANSCRIPTION, true)]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropColumn('transcription');
        });

        // !! Reset the box2 with the old default value for all cards
        $oldDefault = '{
            "version": 1,
            "data": []
        }';
        Card::query()->update(['box2' => $oldDefault]);
    }
};
