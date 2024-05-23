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
        Schema::create('directories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->foreignId('parent_id')->nullable()->constrained('directories')->cascadeOnDelete();
            $table->timestamps();
        });
        
        $root = (new \App\Models\Directory([
            'name' => '/',
        ]));
        $root->save();
        
        $folders = [
            'bin',
            'boot',
            'dev',
            'etc',
            'home',
            'lib',
            'lib64',
            'media',
            'mnt',
            'opt',
            'proc',
            'root',
            'run',
            'sbin',
            'srv',
            'sys',
            'tmp',
            'usr',
            'var',
        ];
        $root->children()->createMany(
            array_map(
                fn($folder) => ['name' => $folder, 'parent_id' => $root->id],
                $folders
            )
        );
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('directories');
    }
};
