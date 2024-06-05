<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $commands = [
            'date',
            'pwd',
            'ls',
            'cd',
            'cat',
            'tracert',
            'clear',
            'cls',
            'get',
        ];
        
        $this->getBinDirectory()
            ->files()->createMany(
                array_map(
                    fn($command) => ['name' => $command],
                    $commands
                )
            );
    }
    
    private function getBinDirectory(): Model
    {
        return \App\Models\Directory::query()->where('name', '=', 'bin')->firstOrFail();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        \App\Models\File::query()->where('directory_id', '=', $this->getBinDirectory()->id)->delete();
    }
};
