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
        Schema::table('files', function (Blueprint $table) {
            $table->boolean('public')->after('directory_id')->default(true);
        });

        (new \App\Models\File([
            'name' => 'flag.txt',
            'content' => 'test',
            'directory_id' => \App\Models\Directory::query()->where('name', '=', 'fanu')->firstOrFail()->id,
            'public' => false,
        ]))->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('files', function (Blueprint $table) {
            $table->dropColumn('public');
        });
        
        \App\Models\File::where('name', 'flag.txt')->delete();
    }
};
