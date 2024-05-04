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
        if ($this->command == 'intro-2')
            return $this->intro2($this->output);
        if ($this->command == 'input')
            return $this->input();

        return $this->output ?? '';
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

    public function intro2(string $string): string {
        $startDate = '2024-04-06';
        $phase3Date = '2024-05-05';
        $phase4Date = '2024-06-08';
        $endDate = '2025-05-22';

        $startDateUnix = (new Carbon($startDate))->unix();
        $endDateUnix = (new Carbon($endDate))->unix();
        $nowUnix = (new Carbon())->unix();
        $phase4DateUnix = (new Carbon($phase4Date))->unix();
        $phase3DateUnix = (new Carbon($phase3Date))->unix();

        $endRelative = $endDateUnix - $startDateUnix;
        $nowRelative = $nowUnix - $startDateUnix;
        $phase4Relative = $phase4DateUnix - $startDateUnix;
        $phase3Relative = $phase3DateUnix - $startDateUnix;

        $nowPercentPrecise = $nowRelative * 100 / $endRelative;
        $phase4PercentPrecise = $phase4Relative * 100 / $endRelative;
        
        $modifier = 1.5;
        $barMax = 100 / $modifier;
        $emptyCharacter = '░';
        $fullCharacter = '█';

        // 75 possibilità al giorno
        // differenza tra il 5/5 e il 8/6 = 34 giorni
        $daysDifference = (new Carbon($phase3Date))->diffInDays($phase4Date);
        // 34 * 75 = 2550
        $oneStep = (100 - $phase4PercentPrecise) / ($daysDifference * 75);
        
        $totalSolvedTests = SessionData::getTotalSolvedTests();
        $percentToAdd = $totalSolvedTests * $oneStep;
        $nowPercentPrecise += $percentToAdd;

        $totalDiskCheckPosition = 547556790632448;
        $currentDiskCheckPosition = (int)($totalDiskCheckPosition / $nowPercentPrecise);
        
        if (true) {
            dd([
                'nowPercentUnmodified' => $nowRelative * 100 / $endRelative,
                'nowPercentPrecise' => $nowPercentPrecise,
                'nowUntilP3Percent' => ($phase3Relative * 100 / $endRelative) - ($nowRelative * 100 / $endRelative),
                'nowUntilP4Percent' => $phase4PercentPrecise - ($nowRelative * 100 / $endRelative),
                'oneStep' => $oneStep,
                'phase4PercentPrecise' => $phase4PercentPrecise,
                'totalSolvedTests' => $totalSolvedTests,
                'percentToAdd' => $percentToAdd,
                'currentDiskCheckPosition' => $currentDiskCheckPosition,
            ]);
        }

        $nowPercent = (int)(($nowPercentPrecise) * 1000) / 1000;
        if ($nowPercent > 100)
            $nowPercent = 100;
        $bar = '[';
        for ($i = 1; $i <= $barMax; $i++) {
            if ($i <= $nowPercent / $modifier)
                $bar .= $fullCharacter;
            else
                $bar .= $emptyCharacter;
        }
        $bar .= ']';

        $footerString = '';
        $sessionData = SessionData::getFromTerminalSession();
        $firstTime = $sessionData->getData('intro-2.first_time_done');
        if (is_null($firstTime)) {
            $sessionData->saveData('intro-2.first_time_done', true);
            $footerString = 'testing' . self::getDeleteString(7, 100);
        }
        $footerString .= 'the system operational';
        $sessionData->saveData('savedCommand', 'startTest');

        return sprintf($string, $currentDiskCheckPosition, $totalDiskCheckPosition, $nowPercent, $bar, $footerString);
    }

    public function input(): string {
        $input = trim($this->args->input);
        $sessionData = SessionData::getFromTerminalSession();
        $globalData = SessionData::getGlobalSession();
        $savedCommand = $sessionData->getData('savedCommand');
        $dailyTestSessionKey = $this->getSessionTestKey();
        $totalSessionTests = $sessionData->getData($dailyTestSessionKey) ?? 0;
        $dailySolvedTests = $globalData->getData($dailyTestSessionKey) ?? 0;
        if ($savedCommand == 'startTest') {
            if ($input == 'Y' || $input == 'y' || $input == ''){
                if ($totalSessionTests >= $this->getMaxSessionTests() || $dailySolvedTests >= $this->getMaxGlobalTests())
                    return "The system is currently overloaded. Please check back in 9§P300§9§P300§9§P300§9§P300§9§P500§9§P500§9§P500§9§P500§9§P800§9§P800§9§P800§9§P800§9\n \n \n \n§DC§";

                $sessionData->saveData('savedCommand', 'continueTest');
                return $this->getRandomTest();
            }

            $sessionData->saveData('savedCommand', null);
            return "Aborted.§P1000§\n \n \n \n§DC§";
        }

        if ($savedCommand == 'continueTest') {
            $testResult = $sessionData->getData('test.testResult');
            if ($input == $testResult || (is_int($testResult) && is_numeric($input) && (int)$input == $testResult)){
                $totalTests = SessionData::getTotalSolvedTests();
                $updatedTotalTests = $totalTests + 1;
                $globalData->saveData('test.solvedTests', $updatedTotalTests);

                $globalData->saveData($dailyTestSessionKey, $dailySolvedTests + 1);
            }

            $sessionData->saveData('savedCommand', null);
            $sessionData->saveData('test.testResult', null);
            $totalSessionTestsUpdated = $totalSessionTests + 1;
            $sessionData->saveData($dailyTestSessionKey, $totalSessionTestsUpdated);
            return "Thank you for your input. There may be additional tasks available to speed up the redundancy check [{$totalSessionTestsUpdated}/{$this->getMaxSessionTests()}].§P1000§\n \n \n \n§DC§";
        }

        return "{$input} is not a recognized command.§DC§";
    }

    private function getMaxSessionTests(): int {
        return 5;
    }

    private function getMaxGlobalTests(): int {
        $phase4Date = '2024-06-08';
        $daysDifference = (new Carbon())->diffInDays($phase4Date);
        // 34 * 75 = 2550
        return (int)(2550 / $daysDifference);
    }

    private function getSessionTestKey(): string {
        $day = (new Carbon())->format('Y-m-d');
        return "test.{$day}.totalTests";
    }

    private function getRandomTest(): string {
        $testTypes = [
            'equation',
            'ascii',
            'currentDay',
        ];
        $testType = $testTypes[array_rand($testTypes)];

        switch ($testType) {
            case 'equation':
                return $this->getEquationTest();
            case 'ascii':
                return $this->getAsciiTest();
            case 'currentDay':
                return $this->getCurrentNumberOfDayTest();
        }

        return '';
    }

    private function getEquationTest(): string {
        $operators = ['+', '-', '*'];
        $operator = $operators[array_rand($operators)];
        $firstNumber = rand(1, 100);
        $secondNumber = rand(1, 100);
        $result = 0;
        switch ($operator) {
            case '+':
                $result = $firstNumber + $secondNumber;
                break;
            case '-':
                $result = $firstNumber - $secondNumber;
                break;
            case '*':
                $result = $firstNumber * $secondNumber;
                break;
        }
        $sessionData = SessionData::getFromTerminalSession();
        $sessionData->saveData('test.testResult', $result);
        return "Resolve this impossible test: {$firstNumber} {$operator} {$secondNumber} = ? §INPUT§";
    }

    private function getAsciiTest(): string {
        $asciiCharacters = [
            'notarobot' => <<<ASCII
              __                          __              __
             /\ \__                      /\ \            /\ \__
  ___     ___\ \ ,_\    __     _ __   ___\ \ \____    ___\ \ ,_\
/' _ `\  / __`\ \ \/  /'__`\  /\`'__\/ __`\ \ '__`\  / __`\ \ \/
/\ \/\ \/\ \L\ \ \ \_/\ \L\.\_\ \ \//\ \L\ \ \ \L\ \/\ \L\ \ \ \_
\ \_\ \_\ \____/\ \__\ \__/.\_\\ \_\\ \____/\ \_,__/\ \____/\ \__\
 \/_/\/_/\/___/  \/__/\/__/\/_/ \/_/ \/___/  \/___/  \/___/  \/__/
ASCII,
            'whytesting' => <<<ASCII
            **                 **                     **   **
           /**       **   **  /**                    /**  //            *****
 ***     **/**      //** **  ******  *****   ****** ****** ** *******  **///**
//**  * /**/******   //***  ///**/  **///** **//// ///**/ /**//**///**/**  /**
 /** ***/**/**///**   /**     /**  /*******//*****   /**  /** /**  /**//******
 /****/****/**  /**   **      /**  /**////  /////**  /**  /** /**  /** /////**
 ***/ ///**/**  /**  **       //** //****** ******   //** /** ***  /**  *****
///    /// //   //  //         //   ////// //////     //  // ///   //  /////
ASCII,
            'help' => <<<ASCII
      ___           ___           ___       ___
     /\__\         /\  \         /\__\     /\  \
    /:/  /        /::\  \       /:/  /    /::\  \
   /:/__/        /:/\:\  \     /:/  /    /:/\:\  \
  /::\  \ ___   /::\~\:\  \   /:/  /    /::\~\:\  \
 /:/\:\  /\__\ /:/\:\ \:\__\ /:/__/    /:/\:\ \:\__\
 \/__\:\/:/  / \:\~\:\ \/__/ \:\  \    \/__\:\/:/  /
      \::/  /   \:\ \:\__\    \:\  \        \::/  /
      /:/  /     \:\ \/__/     \:\  \        \/__/
     /:/  /       \:\__\        \:\__\
     \/__/         \/__/         \/__/
ASCII,
        ];
        $chosenAscii = array_rand($asciiCharacters);

        $sessionData = SessionData::getFromTerminalSession();
        $sessionData->saveData('test.testResult', $chosenAscii);
        return $asciiCharacters[$chosenAscii] . "\n \nWhat is depicted in this pictograph? §INPUT§";
    }

    private function getCurrentNumberOfDayTest(): string {
        $sessionData = SessionData::getFromTerminalSession();
        $currentDay = (new Carbon())->day;
        $sessionData->saveData('test.testResult', $currentDay);
        return "What is the current day of the month? §INPUT§";
    }

    private static function getDeleteString(int $length, int $pause): string {
        return str_repeat("§P{$pause}§█§DEL§§DEL§", $length);
    }
}
