<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('verb_id')->constrained()->onDelete('cascade');
            $table->enum('mode', ['daily', 'endless', 'versus']);
            $table->string('difficulty')->nullable(); // Para endless
            $table->integer('attempts_used');
            $table->boolean('correct')->default(false);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            // Para modos conjugados:
            $table->string('conjugated_form')->nullable();
            $table->string('mood')->nullable();
            $table->string('tense')->nullable();
            $table->string('person')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
