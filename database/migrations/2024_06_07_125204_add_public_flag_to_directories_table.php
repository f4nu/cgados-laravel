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
        Schema::table('directories', function (Blueprint $table) {
            $table->boolean('public')->after('parent_id')->default(false);
        });
        
        $directories = [
            '/',
            'home',
            'var',
            'spool',
            'mail',
        ];
        
        foreach ($directories as $directory) {
            \App\Models\Directory::where('name', $directory)->update(['public' => true]);
        }
        
        (new \App\Models\Directory([
            'name' => 'fanu',
            'parent_id' => \App\Models\Directory::query()->where('name', 'home')->firstOrFail()->id,
            'public' => false,
        ]))->save();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('directories', function (Blueprint $table) {
            $table->dropColumn('public');
        });
        
        \App\Models\Directory::where('name', 'fanu')->delete();
    }
};
