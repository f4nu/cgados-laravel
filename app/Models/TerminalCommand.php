<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TerminalCommand extends Model
{
    use HasFactory;

    protected $primaryKey = 'command';
    protected $keyType = 'string';

    public function parseCommand(): string {
        if ($this->command == 'intro')
            return $this->intro($this->output);

        return $this->output;
    }

    public function intro(string $string): string {
        $totalDiskCheckPosition = 547556790632448;
        $endDate = "2024-05-22";
        $diff = (new Carbon($endDate))->diffInSeconds(new Carbon("2024-04-06"));
        $now = (new Carbon($endDate))->diffInSeconds();
        // currentDisk : totalDisk = diff - now : diff
        $currentDiskCheckPosition = (int)($totalDiskCheckPosition * ($diff - $now) / $diff);
        $percent = (int)($currentDiskCheckPosition / $totalDiskCheckPosition * 10000) / 100;

        $modifier = 1.7;
        $barMax = 100 / $modifier;
        $emptyCharacter = '░';
        $fullCharacter = '█';

        $bar = '[';
        for ($i = 0; $i < $barMax; $i++) {
            if ($i <= $percent / $modifier)
                $bar .= $fullCharacter;
            else
                $bar .= $emptyCharacter;
        }
        $bar .= ']';

        return sprintf($string, $currentDiskCheckPosition, $totalDiskCheckPosition, $percent, $bar);
    }
}
