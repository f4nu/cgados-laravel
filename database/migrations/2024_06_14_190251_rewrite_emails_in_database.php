<?php

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        \App\Models\File::query()->where('directory_id', '=', $this->getMailDirectory()->id)->delete();
        $mailDirectory = $this->getMailDirectory();
        $counter = 0;
        collect(Storage::disk('email')->files())
            ->filter(fn($file) => Str::endsWith($file, '.txt'))
            ->each(function ($file) use ($mailDirectory, &$counter) {
                $content = Storage::disk('email')->get($file);
                (new \App\Models\File([
                    'directory_id' => $mailDirectory->id,
                    'name' => Str::padLeft((string) $counter, 4, '0') . '_' . Str::substr(md5($content), 0, 4),
                    'content' => $content,
                ]))->save();
                $counter++;
            });
    }

    private function getMailDirectory(): Model
    {
        return \App\Models\Directory::query()->where('name', '=', 'mail')->firstOrFail();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('database', function (Blueprint $table) {
            //
        });
    }
};
