<?php

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
        DB::table('terminal_commands')->insert([
            'command' => 'input',
            'force' => 1,
            'enabled' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('terminal_commands')->insert([
            'command' => 'intro-2',
            'output' => <<<DATA
§CLS§Connecting§P1000§.§P1000§.§P1000§. Connected.§P500§

/dev/sda1 contains a file system with errors, check forced.
Inodes that were part of a corrupted orphan linked list found.§P500§


/dev/sda1: UNEXPECTED INCONSISTENCY: STARTING fsck§P500§

fsck exited with status code 4:§P200§ WARNING:§P200§ LIFE SUPPORT NOT OPERATIONAL§P1000§


/dev/sda1: UNEXPECTED INCONSISTENCY: STARTING EMERGENCY REDUNDANCY CHECK


Resuming from position %s/%s (%s%%)
%s§P2000§


WARNING: The system load is currently at maximum capacity, redundancy check
is§P400§ slowed§P500§ down§P500§ by operations beyond§P500§ the ken of automation.§P2000§


External intervention is needed to keep %s.§P1000§


Do you want to start? [Y/n]: §INPUT§
DATA,
            'force' => 0,
            'enabled' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('terminal_commands')->where('command', 'intro')->update(['enabled' => 0]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('terminal_commands')->where('command', 'input')->delete();
        DB::table('terminal_commands')->where('command', 'intro-2')->delete();
        DB::table('terminal_commands')->where('command', 'intro')->update(['enabled' => 1]);
    }
};
