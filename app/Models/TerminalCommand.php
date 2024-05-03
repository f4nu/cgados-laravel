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
            $startDate = '2024-04-06';
            $endDate = '2025-05-22';
            $pinDate = '2024-05-22';

            $startDateUnix = (new Carbon($startDate))->unix();
            $endDateUnix = (new Carbon($endDate))->unix();
            $nowUnix = (new Carbon())->unix();
            $pinDateUnix = (new Carbon($pinDate))->unix();

            $endRelative = $endDateUnix - $startDateUnix;
            $nowRelative = $nowUnix - $startDateUnix;
            $pinRelative = $pinDateUnix - $startDateUnix;

            $nowPercentPrecise = $nowRelative * 100 / $endRelative;
            $nowPercent = (int)(($nowPercentPrecise) * 1000) / 1000;
            $pinPercent = (int)(($pinRelative * 100 / $endRelative) * 1000) / 1000;

            $totalDiskCheckPosition = 547556790632448;
            $currentDiskCheckPosition = (int)($totalDiskCheckPosition / $nowPercentPrecise);

            $modifier = 1.5;
            $barMax = 100 / $modifier;
            $emptyCharacter = '░';
            $fullCharacter = '█';

            $bar = '[';
            for ($i = 1; $i <= $barMax; $i++) {
                if ($i <= $nowPercent / $modifier)
                    $bar .= $fullCharacter;
                else
                    $bar .= $emptyCharacter;
            }
            $bar .= ']';

            $bottomBar = ' ';
            $pinPercentPosition = (int)($pinPercent * $barMax / 100);
            $pinBottomBarActivationThreshold = (int)($pinPercentPosition / $modifier);
            for ($i = 1; $i <= $barMax; $i++) {
                if ($i < $pinBottomBarActivationThreshold)
                    $bottomBar .= ' ';
                else if ($i == $pinBottomBarActivationThreshold)
                    $bottomBar .= '^';
            }

        return sprintf($string, $currentDiskCheckPosition, $totalDiskCheckPosition, $nowPercent, $bar, $bottomBar);
    }
}
