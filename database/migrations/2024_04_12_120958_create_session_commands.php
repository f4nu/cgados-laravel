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
        Schema::create('session_terminal_commands', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('terminal_command_id')->constrained();
            $table->string('terminal_session');
            $table->text('args')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('session_terminal_commands');
    }
};
