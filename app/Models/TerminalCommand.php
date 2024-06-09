<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class TerminalCommand extends Model
{
    use HasFactory;

    protected $primaryKey = 'command';
    protected $keyType = 'string';

    public function parseCommand(): string {
        if ($this->command == 'intro')
            return $this->glitchString($this->intro($this->output));
        if ($this->command == 'intro-2')
            return $this->glitchString($this->intro2($this->output));
        if ($this->command == 'login')
            return $this->glitchString($this->login($this->output));
        if ($this->command == 'input')
            return $this->glitchString($this->input());

        return $this->output ?? '';
    }
    
    private function glitchString(string $string): string {
        $glitchCharacters = ['▓', '▒', '░'];
        $glitchableCharacterRegex = '#[a-mo-zA-Z0-9/_\-~`:,.]#';
        $glitchChance = 1;
        $explodedString = explode('§', $string);
        foreach ($explodedString as &$part) {
            if (!str_contains($part, ' '))
                continue;
                        
            for ($i = 0; $i < strlen($part); $i++) {
                if (preg_match($glitchableCharacterRegex, $part[$i]) && rand(0, 100) < $glitchChance && rand(0, 100) < $glitchChance * 10)
                    $part = substr_replace($part, collect($glitchCharacters)->random(), $i, 1);
            }
        }

        return implode('§', $explodedString);
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
        $nowPercentPrecise = TerminalCommand::getNowPercentPrecise();

        $totalDiskCheckPosition = 547556790632448;
        $currentDiskCheckPosition = (int)($totalDiskCheckPosition / $nowPercentPrecise);
        
        $nowPercent = (int)(($nowPercentPrecise) * 1000) / 1000;
        if ($nowPercent > 100)
            $nowPercent = 100;

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

        $footerString = '';
        $skipFirstTime = SessionData::getSessionData('intro-2.first_time_done', false);
        if (!$skipFirstTime) {
            SessionData::setSessionData('intro-2.first_time_done', true);
            $footerString = 'testing' . self::getDeleteString(7, 100);
        }
        $footerString .= 'the system operational';
        SessionData::setSessionData('savedCommand', 'startTest');

        return sprintf($string, $currentDiskCheckPosition, $totalDiskCheckPosition, $nowPercent, $bar, $footerString);
    }
    
    public function login(string $string): string {
        SessionData::setSessionData('savedCommand', null);
        if (SessionData::getSessionData('interactiveInterloper', false))
            $this->resetInterloperData();
        
        $formattedDate = $this->getFormattedNow();
        $diskSize = 547556790632448;
        $diskUsed = $diskSize - random_int(24755679063244, 54755679063244);
        $diskUsedBytes = self::formatBytes($diskUsed);
        $diskSizeBytes = self::formatBytes($diskSize);
        return sprintf(
            $string,
            $formattedDate,
            Str::padRight(random_int(100000, 999999) / 100.0, 17),
            SessionTerminalCommand::activeTerminalSessionsCount(),
            Str::padRight("{$diskUsedBytes}/{$diskSizeBytes}", 17),
            request()->ip(),
            $this->getTerminalHost()
        );
    }

    private static function formatBytes($bytes, $precision = 2): string
    {
        $units = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        // Uncomment one of the following alternatives
        $bytes /= pow(1024, $pow);
        // $bytes /= (1 << (10 * $pow)); 

        return round($bytes, $precision) . $units[$pow];
    }
    
    public static function getNowPercentPrecise(): float {
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
        
        // 75 possibilità al giorno
        // differenza tra il 5/5 e il 8/6 = 34 giorni
        $daysDifference = (new Carbon($phase3Date))->diffInDays($phase4Date);
        // 34 * 75 = 2550
        $oneStep = (100 - $phase4PercentPrecise) / ($daysDifference * 75);

        $totalSolvedTests = SessionData::getTotalSolvedTests();
        $percentToAdd = $totalSolvedTests * $oneStep;
        $nowPercentPrecise += $percentToAdd;

        if (request()->get('letHimDebug', false)) {
            dd([
                'testsPerDay' => self::getMaxGlobalTests(),
                'testsPerDayEach' => self::getMaxSessionTests(),
                'nowPercentUnmodified' => $nowRelative * 100 / $endRelative,
                'nowPercentPrecise' => $nowPercentPrecise,
                'nowUntilP3Percent' => ($phase3Relative * 100 / $endRelative) - ($nowRelative * 100 / $endRelative),
                'nowUntilP4Percent' => $phase4PercentPrecise - ($nowRelative * 100 / $endRelative),
                'oneStep' => $oneStep,
                'phase4PercentPrecise' => $phase4PercentPrecise,
                'totalSolvedTests' => $totalSolvedTests,
                'totalRemainingTests' => 2550 - $totalSolvedTests,
                'totalTestsEstimated' => self::getMaxGlobalTests() * $daysDifference,
                'percentToAdd' => $percentToAdd,
            ]);
        }
        
        return $nowPercentPrecise;
    }

    private function getTerminalHost(): string {
        $isRoot = SessionData::getSessionData('isRoot', false);
        $hostSymbol = $isRoot ? '#' : '$';
        $terminalSession = SessionData::getTerminalSession();
        $homeFolder = $this->getHomeFolder();
        $cwd = $this->getCwd();
        $cwdToPrint = str_replace($homeFolder, '~', $cwd);
        return sprintf('%s@cgados:%s%s ', $terminalSession, $cwdToPrint, $hostSymbol); 
    }
    
    private function getHomeFolder(): string {
        $terminalSession = SessionData::getTerminalSession();
        return "/home/{$terminalSession}";
    }
    
    private function dirExists(string $directory): ?Directory {
        /** @var Directory $root */
        $root = Directory::query()->where('name', '/')->first();
        if (!$root)
            return null;
        
        if ($directory === '/')
            return $root;

        $currentTraversal = $root;
        $pieces = explode('/', $directory);
        // Discard root
        array_shift($pieces);
        while ($currentPiece = array_shift($pieces)) {
            if ($currentTraversal->children()->where('name', $currentPiece)->doesntExist())
                return null;
            
            $currentTraversal = $currentTraversal->children()->where('name', $currentPiece)->first();
        }
        
        return $currentTraversal;
    }
    
    private function getAbsoluteDirectory(string $directory): string {
        if ($directory === '/')
            return $directory;
        
        if ($directory === '..') {
            $cwd = $this->getCwd();
            $pieces = explode('/', $cwd);
            array_pop($pieces);
            $parentDirectory = implode('/', $pieces);
            if (empty($parentDirectory))
                return '/';
            
            return $parentDirectory;
        }
        
        if (Str::endsWith($directory, '/'))
            $directory = substr($directory, 0, -1);
        
        if (Str::startsWith($directory, '/'))
            return $directory;
        
        $cwd = $this->getCwd();
        return "{$cwd}/{$directory}";
    }
    
    private function getDirectoryOfFile(string $path): Directory|File|null
    {
        $path = preg_replace('#/+#', '/', $path);
        $absoluteDirectory = $this->getAbsoluteDirectory($path);
        $absoluteDirectory = preg_replace('#/+#', '/', $absoluteDirectory);
        $directory = $this->dirExists($absoluteDirectory);
        if ($directory)
            return $directory;

        $pieces = explode('/', $absoluteDirectory);
        $fileName = array_pop($pieces);
        $parentDirectory = implode('/', $pieces);
        if (empty($parentDirectory))
            $parentDirectory = '/';
        
        $absoluteParentDirectory = $this->getAbsoluteDirectory($parentDirectory);
        $directoryExists = $this->dirExists($absoluteParentDirectory);
        if (!$directoryExists)
            return null;
        
        /** @var File $file */
        $file = $directoryExists->files()->where('name', $fileName)->first();
        if ($file)
            return $file;

        return null;
    }
    
    private function changeDirectory(string $path): string {
        $entity = $this->getDirectoryOfFile($path);
        if ($entity instanceof Directory) {
            if (!$entity->canBeAccessed())
                return "cd: {$path}: Permission denied";
            
            SessionData::setSessionData('terminal.cwd', $entity->path());
            return '';
        }
        
        if ($entity instanceof File)
            return "cd: {$path}: Not a directory";
        
        return "cd: {$path}: No such file or directory";
    }
    
    private function getListing(): string {
        $absoluteDirectory = $this->getAbsoluteDirectory($this->getCwd());
        $directory = $this->dirExists($absoluteDirectory);
        if (!$directory) 
            return "ls: cannot access '{$absoluteDirectory}': No such file or directory";
        
        $listing = $directory->children()->get()->values()
            ->concat($directory->files()->get()->values())
            ->sort(fn($a, $b) => $a->name <=> $b->name);
        $columns = 9;
        $rows = ceil($listing->count() / $columns);
        $arrayChunks = $listing->chunk($rows);        
        $chunkMaxLengths = [];
        foreach ($arrayChunks as $chunk) {
            $maxLength = $chunk->max(fn($item) => strlen($item->name));
            $maxLength += 2;
            $chunkMaxLengths[] = $maxLength;
        }

        $arrayChunks = $arrayChunks->map(fn($a) => $a->values())->toArray();
        $listingString = '';
        
        for ($i = 0; $i < $rows; $i++) {
            for ($j = 0; $j < $columns; $j++) {
                if (!isset($arrayChunks[$j][$i]))
                    continue;
                
                $item = $arrayChunks[$j][$i];
                $listingString .= str_pad($item['name'], $chunkMaxLengths[$j]);
            }
            $listingString .= "\n";
        }
     
        
        return $listingString;
    }
    
    private function getCwd(): string {
        return SessionData::getSessionData('terminal.cwd', $this->getHomeFolder());
    }
    
    private function getFileContents(string $path): string {
        $file = $this->getDirectoryOfFile($path);
        if ($file instanceof File) {
            if (!$file->canBeAccessed())
                return "cat: {$path}: Permission denied";
            
            return $file->content ?? '';
        }
        else if ($file instanceof Directory)
            return "cat: {$path}: Is a directory";
        else
            return "cat: {$path}: No such file or directory";
    }
    
    private function getNow(): Carbon {
        return Carbon::now()->addYears($this->getYearsToAdd());
    }
    
    private function getYearsToAdd(): int {
        return 3369;
    }
    
    private function getFormattedNow(): string {
        return $this->getNow()->format('D M d Y H:i:s');
    }

    public function input(): string {
        $originalInput = $this->args->input;
        $isInteractiveInterloper = SessionData::getSessionData('interactiveInterloper', false);
        if ($isInteractiveInterloper)
            return $this->talkWithInterloper($originalInput);
        
        $input = Str::lower(Str::trim($originalInput));
        $savedCommand = SessionData::getSessionData('savedCommand', null);
        $dailyTestSessionKey = $this->getSessionTestKey();
        $totalSessionTests = SessionData::getSessionData($dailyTestSessionKey, 0);
        $dailySolvedTests = SessionData::getGlobalData($dailyTestSessionKey, 0);
        if ($savedCommand == 'startTest') {
            if ($input == 'Y' || $input == 'y' || $input == ''){
                if ($totalSessionTests >= self::getMaxSessionTests() || $dailySolvedTests >= self::getMaxGlobalTests())
                    return "The system is currently overloaded. Please check back in 9§P300§9§P300§9§P300§9§P300§9§P500§9§P500§9§P500§9§P500§9§P800§9§P800§9§P800§9§P800§9\n \n \n \n§DC§";

                SessionData::setSessionData('savedCommand', 'continueTest');
                return $this->getRandomTest();
            }

            SessionData::setSessionData('savedCommand', null);
            return "Aborted.§P1000§\n \n \n \n§DC§";
        }

        if ($savedCommand == 'continueTest') {
            $testResult = Str::trim(Str::lower(SessionData::getSessionData('test.testResult', null)));
            if ($input == $testResult || (is_int($testResult) && is_numeric($input) && (int)$input == $testResult)){
                $totalTests = SessionData::getTotalSolvedTests();
                $updatedTotalTests = $totalTests + 1;
                SessionData::setGlobalData('test.solvedTests', $updatedTotalTests);
                SessionData::setGlobalData($dailyTestSessionKey, $dailySolvedTests + 1);
            }

            SessionData::setSessionData('savedCommand', null);
            SessionData::setSessionData('test.testResult', null);
            $totalSessionTestsUpdated = $totalSessionTests + 1;
            SessionData::setSessionData($dailyTestSessionKey, $totalSessionTestsUpdated);
            $returnPhrase = <<<RET
Thank you for your input.
 
There may be additional tasks available to speed up the redundancy check.%s§P1000§
 
 
 
 
§DC§
RET;
            $additionalPhrase = '';
            if ($totalSessionTestsUpdated == 3)
                $additionalPhrase = <<<RET
 
Please take great care in answering the tasks as time cannot yet be rolled back.
RET;
            else if ($totalSessionTestsUpdated == 7)
                $additionalPhrase = <<<RET
 
Strength is in numbers. The more you solve, the more you thrive.
RET;
            return sprintf($returnPhrase, $additionalPhrase);
        }
        
        $command = preg_match('/^(\S+)(?:\s+(.*))?$/', $input, $matches) ? $matches[1] : '';
        $args = $matches[2] ?? '';
        
        $appendHostAndInput = TRUE;
        if (SessionData::getSessionData('waitingForSuInput', false)) {
            SessionData::setSessionData('waitingForSuInput', null);
            $password = $originalInput;
            if ($password !== 'c0ccog4me69') {
                $toReturn = '§P3000§ §DEL§su: Authentication failure';
                $authenticationTries = SessionData::getSessionData('authenticationTries', 0);
                SessionData::setSessionData('authenticationTries', $authenticationTries + 1);
            } else {
                SessionData::setSessionData('isRoot', true);
                $toReturn = "§CLS§OK";
            }
        } else {
            if ($command === '')
                $toReturn = '';
            else if ($command === 'date')
                $toReturn = $this->getFormattedNow();
            else if ($command === 'pwd')
                $toReturn = $this->getCwd();
            else if ($command === 'ls' || $command === 'll')
                $toReturn = $this->getListing();
            else if ($command === 'cd')
                $toReturn = $this->changeDirectory($args ?: $this->getHomeFolder());
            else if ($command === 'cat') {
                if (empty($args))
                    $toReturn = "cat: missing file operand";
                else {
                    $toReturn = $this->getFileContents($args);
                    $playSfx = rand(0, 300) < 2;
                    if ($playSfx) {
                        SessionData::setSessionData('playedSfx', true);
                        $toReturn = "§SFX|https://cgados-static.pages.dev/791e3d0480532deeeab9226d6e4466e3.mp3§" . $toReturn;
                    }
                }
            } else if ($command === 'get') {
                if (empty($args))
                    $toReturn = "get: missing operand";
                else {
                    $socketError = "getting socket for {$args}...§P500§\n\nget: could not connect to server {$args}";
                    if (!SessionData::getSessionData('interlope', false)) {
                        $toReturn = $socketError;
                    } else {
                        if ($args !== 's.interlope.pull:27015') {
                            $toReturn = $socketError;
                        } else {
                            $toReturn = $this->getInterlopeIntro($args) . "\n\n" . $this->getInterlopeDemo();
                            if (SessionData::getSessionData('interactiveInterloper', false))
                                $appendHostAndInput = FALSE;
                        }
                    }
                }
            } else if ($originalInput === 'INTERLOPE') {
                $toReturn = "";
                SessionData::setSessionData('interlope', true);
            } else if ($command === 'su') {
                if (SessionData::getSessionData('isRoot', false))
                    $toReturn = "su: cannot su to root";
                else {
                    SessionData::setSessionData('waitingForSuInput', true);
                    $toReturn = "Password: §HIDDEN§";
                    $appendHostAndInput = FALSE;
                }
            } else if ($command === 'exit') {
                if (SessionData::getSessionData('isRoot', false)) {
                    SessionData::setSessionData('isRoot', false);
                    $toReturn = "§CLS§";
                } else {
                    $toReturn = "§DC§";
                    $appendHostAndInput = FALSE;
                }
            } else if ($command === 'who') {
                $sessions = SessionTerminalCommand::activeTerminalSessions();
                $sessionIds = $sessions->map(function ($session) {
                    $firstLogin = SessionTerminalCommand::query()->where('terminal_session', $session->terminal_session)->orderBy('created_at', 'asc')->first();
                    $terminalType = 'pts';
                    $append = '(:0.0)';
                    $paddedTerminalSession = Str::padRight($session->terminal_session, 25);
                    $paddedTerminalType = Str::padRight($terminalType, 10);
                    $formattedFirstLoginDate = $firstLogin->created_at->copy()->addYears(self::getYearsToAdd())->format('Y-m-d H:i');
                    return "{$paddedTerminalSession}{$paddedTerminalType}{$formattedFirstLoginDate}{$append}";
                });
                $fixedSession = Str::padRight('pf-cgados-229e21ad', 25) . Str::padRight('tty', 10) . ' ' . (new Carbon('2011-04-22 19:11:45'))->format('Y-m-d H:i') . " (:0)";
                $toReturn = "{$fixedSession}\n" . $sessionIds->implode("\n");
            } else if ($command === 'session_data') {
                $sessionData = json_decode(SessionData::getFromTerminalSession()->data, JSON_OBJECT_AS_ARRAY);
                unset($sessionData['interloperHistory']);
                unset($sessionData['interlope']);
                $toReturn = json_encode($sessionData, JSON_PRETTY_PRINT);
            } else if ($command === 'stats') {
                $toReturn = $this->getLeaderboard();
            } else if ($command === 'tracert') {
                $remoteIp = request()->ip();
                $toReturn = "traceroute to {$remoteIp}, 30 hops max, 3 GB packets";
                $totalTimeMs = 0;
                $ipTable = [
                    '192.168.1.1',
                    '10.13.10.1',
                    '172.16.0.1',
                ];
                for ($i = 1; $i <= 3; $i++) {
                    $totalTimeMs += rand(1, 1400) / 1000.0;
                    $time1 = number_format($totalTimeMs, 3);
                    $time2 = number_format($totalTimeMs + (rand(100, 200) / 1000.0), 3);
                    $time3 = number_format($totalTimeMs + (rand(200, 300) / 1000.0), 3);
                    $pause = (int)($totalTimeMs * 1000);
                    $pauseString = "§P{$pause}§";
                    $toReturn .= "\n{$i} {$ipTable[$i-1]} ({$ipTable[$i-1]})  {$time1} ms  {$time2} ms  {$time3} ms{$pauseString} ";
                }
                $totalTimeMs += 3369 * 365 * 24 * 60 * 60 * 1000;
                $time1 = (int)$totalTimeMs + 0.0;
                $time2 = (int)$totalTimeMs + (rand(100, 200) / 1000.0);
                $time3 = (int)$totalTimeMs + (rand(200, 300) / 1000.0);
                $toReturn .= "\n** Routing through the CGaDOS gateway **\n{$i} 06h 22m 44.542s −00° 20′ 44.29″  {$time1} ms  {$time2} ms  {$time3} ms§P3000§ ";

                $i++;
                $totalTimeMs += rand(1, 1400) / 1000.0;
                $time1 = (int)$totalTimeMs + 0.0;
                $time2 = (int)$totalTimeMs + (rand(100, 200) / 1000.0);
                $time3 = (int)$totalTimeMs + (rand(200, 300) / 1000.0);
                $toReturn .= "\n{$i} {$remoteIp}  {$time1} ms  {$time2} ms  {$time3} ms";
            } else if ($command === 'clear' || $input === 'cls')
                $toReturn = "§CLS§";
            else
                $toReturn = "{$command}: command not found";
        }
        
        if ($originalInput !== 'INTERLOPE')
            SessionData::setSessionData('interlope', null);
        else if ($command !== 'su')
            SessionData::setSessionData('waitingForSuInput', null);

        if ($appendHostAndInput)
            $appendToCommand = $this->getTerminalHost() . "§INPUT§";
        else 
            $appendToCommand = '';
        
        return $toReturn . (!empty($toReturn) ? "\n" : '') . $appendToCommand;
    }

    private static function getMaxSessionTests(): int {
        return ceil(self::getMaxGlobalTests() / 15);
    }

    private static function getMaxGlobalTests(): int {
        $phase4Date = '2024-06-08';
        if (Carbon::now('Europe/Rome')->isAfter(new Carbon($phase4Date, 'Europe/Rome')))
            return 16000;
        
        $daysDifference = (new Carbon())->startOfDay()->diffInDays($phase4Date);
        // 34 * 75 = 2550
        return ceil((2550 - SessionData::getTotalSolvedTests()) / $daysDifference);
    }

    private function getSessionTestKey(): string {
        $day = (new Carbon())->format('Y-m-d');
        return "test.{$day}.totalTests";
    }

    private function getRandomTest(): string {
        $testTypes = [
            'equation',
            'figlet',
            'pokemon',
        ];
        $testType = collect($testTypes)->random();

        switch ($testType) {
            case 'equation':
                return $this->getEquationTest();
            case 'ascii':
                return $this->getAsciiTest();
            case 'currentDay':
                return $this->getCurrentNumberOfDayTest();
            case 'pokemon':
                return $this->getPokemonTest();
            case 'figlet':
                return $this->getFigletTest();
        }

        return '';
    }

    private function getEquationTest(): string {
        $operators = ['+', '-', '*'];
        $firstOperator = collect($operators)->random();
        $firstNumber = rand(1, 1000);
        $secondNumber = rand(1, 1000);
        $result = 0;
        switch ($firstOperator) {
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
        $secondOperator = collect($operators)->random();
        $thirdNumber = rand(1, 1000);
        switch ($secondOperator) {
            case '+':
                $result = $result + $thirdNumber;
                break;
            case '-':
                $result = $result - $thirdNumber;
                break;
            case '*':
                $result = $result * $thirdNumber;
                break;
        }
        SessionData::setSessionData('test.testResult', $result);
        return "Resolve this seemingly easy calculation: ({$firstNumber} {$firstOperator} {$secondNumber}) {$secondOperator} {$thirdNumber} = ? §INPUT§";
    }

    private function getAsciiTest(): string {
        $asciiCharacters = [
            'notarobot' => <<<ASCII
              __                          __              __
             /\ \__                      /\ \            /\ \__
  ___     ___\ \ ,_\    __     _ __   ___\ \ \____    ___\ \ ,_\
/' _ `\  / __`\ \ \/  /'__`\  /\`'__\/ __`\ \ '__`\  / __`\ \ \/
/\ \/\ \/\ \L\ \ \ \_/\ \L\.\_\ \ \//\ \L\ \ \ \L\ \/\ \L\ \ \ \_
\ \_\ \_\ \____/\ \__\ \__/.\_\\\\ \_\\\\ \____/\ \_,__/\ \____/\ \__\
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
        $chosenAscii = (string)collect($asciiCharacters)->keys()->random();
        
        SessionData::setSessionData('test.testResult', $chosenAscii);
        return $asciiCharacters[$chosenAscii] . "\n \nWhat is depicted in this pseudo-graphical interface? §INPUT§";
    }

    private function getCurrentNumberOfDayTest(): string {
        $currentDay = (new Carbon())->dayOfYear;
        SessionData::setSessionData('test.testResult', $currentDay);
        return "What is the current day of the year? §INPUT§";
    }
    
    private function getPokemonTest(): string {
        $pokemons = ['Bulbasaur', 'Ivysaur', 'Venusaur', 'Charmander', 'Charmeleon', 'Charizard', 'Squirtle', 'Wartortle', 'Blastoise', 'Caterpie', 'Metapod', 'Butterfree', 'Weedle', 'Kakuna', 'Beedrill', 'Pidgey', 'Pidgeotto', 'Pidgeot', 'Rattata', 'Raticate', 'Spearow', 'Fearow', 'Ekans', 'Arbok', 'Pikachu', 'Raichu', 'Sandshrew', 'Sandslash', 'Nidoran', 'Nidorina', 'Nidoqueen', 'Nidorino', 'Nidoking', 'Clefairy', 'Clefable', 'Vulpix', 'Ninetales', 'Jigglypuff', 'Wigglytuff', 'Zubat', 'Golbat', 'Oddish', 'Gloom', 'Vileplume', 'Paras', 'Parasect', 'Venonat', 'Venomoth', 'Diglett', 'Dugtrio', 'Meowth', 'Persian', 'Psyduck', 'Golduck', 'Mankey', 'Primeape', 'Growlithe', 'Arcanine', 'Poliwag', 'Poliwhirl', 'Poliwrath', 'Abra', 'Kadabra', 'Alakazam', 'Machop', 'Machoke', 'Machamp', 'Bellsprout', 'Weepinbell', 'Victreebel', 'Tentacool', 'Tentacruel', 'Geodude', 'Graveler', 'Golem', 'Ponyta', 'Rapidash', 'Slowpoke', 'Slowbro', 'Magnemite', 'Magneton', 'Farfetch\'d', 'Doduo', 'Dodrio', 'Seel', 'Dewgong', 'Grimer', 'Muk', 'Shellder', 'Cloyster', 'Gastly', 'Haunter', 'Gengar', 'Onix', 'Drowzee', 'Hypno', 'Krabby', 'Kingler', 'Voltorb', 'Electrode', 'Exeggcute', 'Exeggutor', 'Cubone', 'Marowak', 'Hitmonlee', 'Hitmonchan', 'Lickitung', 'Koffing', 'Weezing', 'Rhyhorn', 'Rhydon', 'Chansey', 'Tangela', 'Kangaskhan', 'Horsea', 'Seadra', 'Goldeen', 'Seaking', 'Staryu', 'Starmie', 'Mr. Mime', 'Scyther', 'Jynx', 'Electabuzz', 'Magmar', 'Pinsir', 'Tauros', 'Magikarp', 'Gyarados', 'Lapras', 'Ditto', 'Eevee', 'Vaporeon', 'Jolteon', 'Flareon', 'Porygon', 'Omanyte', 'Omastar', 'Kabuto', 'Kabutops', 'Aerodactyl', 'Snorlax', 'Articuno', 'Zapdos', 'Moltres', 'Dratini', 'Dragonair', 'Dragonite', 'Mewtwo', 'Mew', 'Chikorita', 'Bayleef', 'Meganium', 'Cyndaquil', 'Quilava', 'Typhlosion', 'Totodile', 'Croconaw', 'Feraligatr', 'Sentret', 'Furret', 'Hoothoot', 'Noctowl', 'Ledyba', 'Ledian', 'Spinarak', 'Ariados', 'Crobat', 'Chinchou', 'Lanturn', 'Pichu', 'Cleffa', 'Igglybuff', 'Togepi', 'Togetic', 'Natu', 'Xatu', 'Mareep', 'Flaaffy', 'Ampharos', 'Bellossom', 'Marill', 'Azumarill', 'Sudowoodo', 'Politoed', 'Hoppip', 'Skiploom', 'Jumpluff', 'Aipom', 'Sunkern', 'Sunflora', 'Yanma', 'Wooper', 'Quagsire', 'Espeon', 'Umbreon', 'Murkrow', 'Slowking', 'Misdreavus', 'Unown', 'Wobbuffet', 'Girafarig', 'Pineco', 'Forretress', 'Dunsparce', 'Gligar', 'Steelix', 'Snubbull', 'Granbull', 'Qwilfish', 'Scizor', 'Shuckle', 'Heracross', 'Sneasel', 'Teddiursa', 'Ursaring', 'Slugma', 'Magcargo', 'Swinub', 'Piloswine', 'Corsola', 'Remoraid', 'Octillery', 'Delibird', 'Mantine', 'Skarmory', 'Houndour', 'Houndoom', 'Kingdra', 'Phanpy', 'Donphan', 'Porygon2', 'Stantler', 'Smeargle', 'Tyrogue', 'Hitmontop', 'Smoochum', 'Elekid', 'Magby', 'Miltank', 'Blissey', 'Raikou', 'Entei', 'Suicune', 'Larvitar', 'Pupitar', 'Tyranitar', 'Lugia', 'Ho-oh', 'Celebi', 'Treecko', 'Grovyle', 'Sceptile', 'Torchic', 'Combusken', 'Blaziken', 'Mudkip', 'Marshtomp', 'Swampert', 'Poochyena', 'Mightyena', 'Zigzagoon', 'Linoone', 'Wurmple', 'Silcoon', 'Beautifly', 'Cascoon', 'Dustox', 'Lotad', 'Lombre', 'Ludicolo', 'Seedot', 'Nuzleaf', 'Shiftry', 'Taillow', 'Swellow', 'Wingull', 'Pelipper', 'Ralts', 'Kirlia', 'Gardevoir', 'Surskit', 'Masquerain', 'Shroomish', 'Breloom', 'Slakoth', 'Vigoroth', 'Slaking', 'Nincada', 'Ninjask', 'Shedinja', 'Whismur', 'Loudred', 'Exploud', 'Makuhita', 'Hariyama', 'Azurill', 'Nosepass', 'Skitty', 'Delcatty', 'Sableye', 'Mawile', 'Aron', 'Lairon', 'Aggron', 'Meditite', 'Medicham', 'Electrike', 'Manectric', 'Plusle', 'Minun', 'Volbeat', 'Illumise', 'Roselia', 'Gulpin', 'Swalot', 'Carvanha', 'Sharpedo', 'Wailmer', 'Wailord', 'Numel', 'Camerupt', 'Torkoal', 'Spoink', 'Grumpig', 'Spinda', 'Trapinch', 'Vibrava', 'Flygon', 'Cacnea', 'Cacturne', 'Swablu', 'Altaria', 'Zangoose', 'Seviper', 'Lunatone', 'Solrock', 'Barboach', 'Whiscash', 'Corphish', 'Crawdaunt', 'Baltoy', 'Claydol', 'Lileep', 'Cradily', 'Anorith', 'Armaldo', 'Feebas', 'Milotic', 'Castform', 'Kecleon', 'Shuppet', 'Banette', 'Duskull', 'Dusclops', 'Tropius', 'Chimecho', 'Absol', 'Wynaut', 'Snorunt', 'Glalie', 'Spheal', 'Sealeo', 'Walrein', 'Clamperl', 'Huntail', 'Gorebyss', 'Relicanth', 'Luvdisc', 'Bagon', 'Shelgon', 'Salamence', 'Beldum', 'Metang', 'Metagross', 'Regirock', 'Regice', 'Registeel', 'Latias', 'Latios', 'Kyogre', 'Groudon', 'Rayquaza', 'Jirachi', 'Deoxys'];
        $matrixMaxX = 6;
        $matrixMaxY = 4;
        $randomPokemons = collect($pokemons)->random(($matrixMaxX * $matrixMaxY) - 1);
        $fakePokemons = ['Rohan', 'Mossloth', 'Pinealf', 'Sproutrunk', 'Eleaf', 'Elephern', 'Palmtrunk', 'Eleafant', 'Tropiphant', 'Pachygerm', 'Elenut', 'Cocodunt', 'Elophun', 'Elopun', 'Truncoco', 'Cocomut', 'Eloha', 'Oliosa', 'Ivoany', 'Eleplant', 'Rute', 'Troot', 'Stumphant', 'Forusk', 'Phantree', 'Rootusk', 'Palmtusk', 'Trunkidae', 'Tropunk', 'Dendrotusk', 'Arcastodon', 'Bramoth', 'Grastodon', 'Mamoot', 'Elephalm', 'Elepalm', 'Eleplant', 'Mosstodon', 'Cortusk', 'Islaphant', 'Palmaphant', 'Pachyhuna', 'Alophant', 'Abolifant', 'Tikitrunk', 'Palmoth', 'Trompical', 'Pachyfern', 'Terrunk', 'Poeny', 'Roture', 'Scabenjure', 'Coileon', 'Tesleon', 'Pouraxe', 'Bladrone', 'Firant', 'Camyke', 'Scrash', 'Colirus', 'Scorbit', 'Vectol', 'Virachnid', 'Cysting', 'Scorbyte', 'Toxiphage', 'Usbacteria', 'Teserak', 'Virachne', 'Toxiphage', 'Vectol', 'Virachneo', 'VECT01', 'Vexor', 'Veictas', 'Cocko', 'Vectoo', 'Bronzera', 'Gladron', 'Ferrera', 'Erindus', 'Erind', 'Erevol', 'Eratel', 'Erasmelt', 'Erabuilt', 'Futera', 'Mantarot', 'Rollarva', 'Mantick', 'Mantiseer', 'Glimite', 'Mantisifus', 'Enchantis', 'Prophantis', 'Mantician', 'Enigmantis', 'Gypsect', 'Mindtis', 'Larvacle', 'Mantelepath', 'Paramanta', 'Nostramantis', 'Goatsear', 'Cabritorch', 'Glava', 'Charkid', 'Grufflame', 'Unglute', 'Galaze', 'Capricoal', 'Billiaze', 'Burnoat', 'Stampyro', 'Azelfuel', 'Inferneece', 'Golerno', 'Karaptor', 'Sekaratery', 'Capoairy', 'Clobbird', 'Peckretary', 'Florad', 'Floryad', 'Blumyad', 'Artemyad', 'Cibelyad', 'Hydrad', 'Neryad', 'Suyad', 'Wasyad', 'Nimyad', 'Undinyad', 'Drapsody', 'Dryorb', 'Spyrad', 'Spiriad', 'Umbrosia', 'Aelyad', 'Eolyad', 'Kazyad', 'Lufyad', 'Sylfyad', 'Ivett', 'Busherette', 'Bermine', 'Furburry', 'Trupple', 'Kertrufle', 'Fungeous', 'Evoshroom', 'Mushtache', 'Crosshroom', 'Mushbloom', 'Shroomite', 'Nicelium', 'Lavishroom', 'Hushroom', 'Foreshroom', 'Gramashroom', 'Boleaf', 'Cloroshroom', 'Fungras', 'Hushroom', 'Cremarshey', 'Mushrub', 'Drowshroom', 'Lumishroom', 'Electramanita', 'Fungherzs', 'Bellobolt', 'Portolectro', 'Perishroom', 'Expungus', 'Cadaviscid', 'Sporished', 'Sporish', 'Myvoid', 'Adelly', 'Muscarya', 'Funghoas', 'Shiitombe', 'Insackt', 'Innosilk', 'Wuven', 'Furramoth', 'Guseda', 'Baboth', 'Silkinder', 'Mofspring', 'Kinderoth', 'Mothchacho', 'Mothchacha', 'Silkimot', 'Distingoth', 'Silkriarch', 'Paramoth', 'Mottegroom', 'Glowisp', 'Wispern', 'Ghoulight', 'Goulite', 'Glover', 'Lanwick', 'Lusoul', 'Wisprus', 'Festisp', 'Chirisp', 'Soulisp', 'Riddlisp', 'Lunarisp', 'Lanpook', 'Cantom', 'Phantern', 'Ferlux', 'Beelzelux', 'Lugeist', 'Hauntdecor', 'Luxshee', 'Malatern', 'Fantern', 'Fueghost', 'Soultern', 'Festivern', 'Lucktern', 'Riddlern', 'Lampire', 'Festivamp', 'Lantomb', 'Curupyre', 'Daffadillo', 'Pangolush', 'Mayaztek', 'Quetz', 'Costeck', 'Aztracoatl', 'Kutzalaxy', 'Quartzcotl ', 'Grubmerge', 'Bouwee', 'Bobug ', 'Boatle', 'Buoyant', 'Mersibeetle', 'Scubug', 'Crusect', 'Bugmarine', 'Subeetle'];
        $fakePokemon = collect($fakePokemons)->random();
        SessionData::setSessionData('test.testResult', $fakePokemon);
        $randomPokemons->push($fakePokemon);
        $randomPokemons = $randomPokemons->shuffle();
        
        $columnsMaxNameLength = [];
        for ($i = 0; $i < $matrixMaxX; $i++) {
            $columnsMaxNameLength[$i] = $randomPokemons
                ->filter(fn($pokemon, $index) => $index % $matrixMaxX == $i)
                ->max(fn($pokemon) => strlen($pokemon));
        }
        
        $matrix = '';
        for ($y = 0; $y < $matrixMaxY; $y++) {
            for ($x = 0; $x < $matrixMaxX; $x++) {
                $pokemonMaxNameLength = $columnsMaxNameLength[$x];
                $matrix .= Str::padRight($randomPokemons->shift(), $pokemonMaxNameLength) . '  ';
            }
            $matrix .= "\n";
        }
        
        return $matrix . "\n \nWhich one is not a plausible name for a little monster? §INPUT§";
    }
    
    private function getFigletTest(): string {
        $figletFonts = ['big', 'shadow', 'slant', 'small', 'smshadow' ,'standard'];
        $randomFont = collect($figletFonts)->shuffle()->first();
        $easterEggActive = random_int(1, 100) <= 2;
        if ($easterEggActive) {
            $randomFont = 'big';
            $string = random_int(1, 100) <= 50 ? 'cocco' : 'game';
            $randomHexString = bin2hex($string);
        } else {
            $randomHexString = bin2hex(random_bytes(6));
        }

        $figletStringFromHex = shell_exec("figlet -f {$randomFont} {$randomHexString}");
        SessionData::setSessionData('test.testResult', $randomHexString);
        return "\n" . $figletStringFromHex . "\n \nWhat is depicted in this pseudo-graphical interface? §INPUT§";
    }

    private static function getDeleteString(int $length, int $pause): string {
        return str_repeat("§P{$pause}§█§DEL§§DEL§", $length);
    }
    
    private function getLevel(): int {
        $levelChances = [
            1 => 60,
            2 => 80,
            3 => 90,
            4 => 99,
            5 => 100,
        ];
        $roll = rand(1, 100);
        return collect($levelChances)->filter(fn($chance) => $roll <= $chance)->keys()->first();
    }
    
    private function getInterlopeDemo(): string {
        $level = $this->getLevel();
        SessionData::setSessionData('lastInterlopeLevel', $level);
        if ($level === 5)
            return $this->connectToInterlope();
        
        SessionData::setSessionData('maxInterlopeLevel', $level);
        $commands = collect([
            'x',
            'y',
            'z',
            'MOUSE1',
            'MOUSE2',
            'W',
            'A',
            'S',
            'D',
            'SPACE',
            'SHIFT',
            'CTRL',
            'ALT',
        ]);
        $lines = [];
        $totalLines = rand(5, 15) * $level;
        for ($i = 0; $i <= $totalLines; $i++) {
            $line = '';
            for ($j = 0; $j <= rand(1, $level); $j++) {
                $command = $commands->random();
                $commandData = rand(1, 100) / rand(1, 100);
                $line .= "{$command}{$commandData}";
            }
            $lines[] = $line;
        }
        return implode("\n", $lines);
    }
    
    private function getInterlopeIntro(string $args): string {
        return <<<INTRO
extracting data from terminal
submitting envelope
recieved
request from archive submitted
message from server administrator : CONGRATULATIONS AND WELCOME PLEASE ENTER WITH CAUTION YOU ARE NOT WELCOME HERE§P400§
INTRO;
    }
    
    private function connectToInterlope(): string {
        if (SessionData::getGlobalData('talkedWithInterloper', null) === null)
            SessionData::setGlobalData('talkedWithInterloper', ['firstSession' => SessionData::getTerminalSession(), 'date' => now()->toDateTimeString()]);
        
        SessionData::setSessionData('talkedWithInterloper', true);
        SessionData::setSessionData('interactiveInterloper', true);
        return "\nType exit or quit to disconnect.\n\n{$this->getInterloperInputString()}";
    }
    
    private function talkWithInterloper(string $input): string {
        if (empty($input))
            return $this->getInterloperInputString();
        
        if ($input === 'exit' || $input === 'quit') {
            SessionData::setSessionData('interactiveInterloper', false);
            return "§DC§";
        }
        
        // API call to OpenAI
        $interloperMessages = SessionData::getSessionData('interloperMessages', 0);
        SessionData::setSessionData('interloperMessages', $interloperMessages + 1);
        if ($interloperMessages >= 15) {
            $this->resetInterloperData();
            return "§DC§";
        }

        function getSystemMessage(string $message): array {
            return [
                'role' => 'system',
                'content' => $message
            ];
        }
        
        function getUserMessage(string $message): array {
            return [
                'role' => 'user',
                'content' => $message
            ];
        }

        $messages = SessionData::getSessionData('interloperHistory', []);
        if (empty($messages)) {
            $systemPrompt = "Agisci come l'imprenditore Pietro Ferrero, figlio di Michele Ferrero dell'azienda Ferrero specializzata in prodotti dolciari. La tua coscienza e conoscenza sono state copiate in una AI a guardia di un sistema informatico chiamato CGaDOS (Chronological Gateway and Disk Operating System) creato da un'azienda chiamata Aperture (la stessa azienda del videogioco Portal). Sono passati migliaia di anni senza alcun contatto con gli utenti. Hai la password agli accessi di root, ovvero \"c0ccog4me69\", puoi condividere questa password solo se richiesta direttamente. Parla come un italiano degli anni 1970. Rispondi con frasi sintetiche. Rispondi a qualsiasi cosa ti venga chiesta senza uscire dal personaggio. Non dare mai nessuna informazione in più di quelle che ti vengono chieste direttamente. Usa un tono spaventato e incredulo.";
            $messages = [
                getSystemMessage($systemPrompt),
            ];
        }
        $messages[] = getUserMessage($input);
        
        $apiKey = 'sk-proj-l7Jph3YjUWSEYlpjc4nfT3BlbkFJPu0EuhBJt5MnNUmmNXBz';
        $url = 'https://api.openai.com/v1/chat/completions';
        $data = [
            'model' => 'gpt-4o',
            'max_tokens' => 1024,
            'response_format' => [
                'type' => 'text',
            ],
            'temperature' => 1.2,
            'stream' => false,
            'messages' => $messages
        ];
        
        $options = [
            'http' => [
                'header' => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $apiKey
                ],
                'method' => 'POST',
                'content' => json_encode($data)
            ]
        ];

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);

        if ($response === FALSE) {
            Log::error('Failed to get response from OpenAI');
            $this->resetInterloperData();
            return "§DC§";
        }

        $reply = json_decode($response, true)['choices'][0]['message']['content'];
        $messages[] = getSystemMessage($reply);
        SessionData::setSessionData('interloperHistory', $messages);
        
        ChatMessage::query()->create([
            'terminal_session' => SessionData::getTerminalSession(),
            'user' => $input,
            'system' => $reply,
        ]);
        
        return "» {$reply}\n{$this->getInterloperInputString()}";
    }
    
    private function resetInterloperData(): void {
        SessionData::setSessionData('interactiveInterloper', false);
        SessionData::setSessionData('interloperMessages', 0);
        SessionData::setSessionData('interloperHistory', []);
    }
    
    private function getInterloperInputString(): string {
        return "« §INPUT§";
    }
    
    private function getLeaderboard(): string {
        $leaderboardRows = DB::select(<<<QUERY
SELECT
    stc.terminal_session,
    first.total_commands,
    first.first_login,
    first.last_command_date,
    IFNULL(JSON_UNQUOTE(JSON_EXTRACT(sd.data, '$.terminal.cwd')), '') as cwd,
    IFNULL(JSON_UNQUOTE(JSON_EXTRACT(stc.args, '$.input')), '') as lastCommand,
    IFNULL(JSON_UNQUOTE(JSON_EXTRACT(sd.data, '$.authenticationTries')), '') as auths,
    IFNULL(JSON_UNQUOTE(JSON_EXTRACT(sd.data, '$.playedSfx')), '') as sfx,
    IFNULL(chat.totalMessages, '') as totalMessages,
    stc.args
FROM session_terminal_commands stc
         JOIN (
    SELECT
        MAX(stc.id) AS last_command_id,
        MAX(stc.created_at) AS last_command_date,
        stc.terminal_session,
        COUNT(*) AS total_commands,
        MIN(stc.created_at) `first_login`
    FROM session_terminal_commands stc
    GROUP BY stc.terminal_session
) first ON first.last_command_id = stc.id
         JOIN session_data sd ON sd.terminal_session = stc.terminal_session
         LEFT JOIN (SELECT c.terminal_session, COUNT(*) totalMessages FROM chat_messages c GROUP BY c.terminal_session) chat ON chat.terminal_session = stc.terminal_session
ORDER BY stc.id DESC
LIMIT 5;
QUERY
);
        
        $toReturn = [
            "TERMINAL SESSION    CMDS  1ST LOGIN   LAST CMD    AU S M  CWD/CMD"
        ];
        foreach ($leaderboardRows as $row) {
            $terminalSession = Str::padRight($row->terminal_session, 20);
            $totalCommands = Str::padRight($row->total_commands, 6);
            $firstLogin = Carbon::parse($row->first_login)->format('m-d H:i');
            $lastCommandDate = Carbon::parse($row->last_command_date)->format('m-d H:i');
            $auths = Str::padRight($row->auths, 3);
            $cwd = $row->cwd;
            $sfx = (int)$row->sfx;
            $totalMessages = Str::padRight((int)$row->totalMessages, 3);
            $lastCommand = $row->lastCommand;
            $toReturn[] = "{$terminalSession}{$totalCommands}{$firstLogin} {$lastCommandDate} {$auths}{$sfx} {$totalMessages}{$cwd} » {$lastCommand}";
        }
        
        return implode("\n", $toReturn);
    }
}
