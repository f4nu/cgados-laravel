<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
        
        if (request()->get('letHimDebug', false)) {
            dd([
                'testsPerDay' => $this->getMaxGlobalTests(),
                'testsPerDayEach' => $this->getMaxSessionTests(),
                'nowPercentUnmodified' => $nowRelative * 100 / $endRelative,
                'nowPercentPrecise' => $nowPercentPrecise,
                'nowUntilP3Percent' => ($phase3Relative * 100 / $endRelative) - ($nowRelative * 100 / $endRelative),
                'nowUntilP4Percent' => $phase4PercentPrecise - ($nowRelative * 100 / $endRelative),
                'oneStep' => $oneStep,
                'phase4PercentPrecise' => $phase4PercentPrecise,
                'totalSolvedTests' => $totalSolvedTests,
                'totalRemainingTests' => 2550 - $totalSolvedTests,
                'totalTestsEstimated' => $this->getMaxGlobalTests() * $daysDifference,
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
        $skipFirstTime = SessionData::getSessionData('intro-2.first_time_done', false);
        if (!$skipFirstTime) {
            SessionData::setSessionData('intro-2.first_time_done', true);
            $footerString = 'testing' . self::getDeleteString(7, 100);
        }
        $footerString .= 'the system operational';
        SessionData::setSessionData('savedCommand', 'startTest');

        return sprintf($string, $currentDiskCheckPosition, $totalDiskCheckPosition, $nowPercent, $bar, $footerString);
    }

    public function input(): string {
        $input = Str::lower(Str::trim($this->args->input));
        $savedCommand = SessionData::getSessionData('savedCommand', null);
        $dailyTestSessionKey = $this->getSessionTestKey();
        $totalSessionTests = SessionData::getSessionData($dailyTestSessionKey, 0);
        $dailySolvedTests = SessionData::getGlobalData($dailyTestSessionKey, 0);
        if ($savedCommand == 'startTest') {
            if ($input == 'Y' || $input == 'y' || $input == ''){
                if ($totalSessionTests >= $this->getMaxSessionTests() || $dailySolvedTests >= $this->getMaxGlobalTests())
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
            return "Thank you for your input.\n \nThere may be additional tasks available to speed up the redundancy check.§P1000§\n \n \n \n§DC§";
        }

        return "{$input} is not a recognized command.§DC§";
    }

    private function getMaxSessionTests(): int {
        return ceil($this->getMaxGlobalTests() / 15);
    }

    private function getMaxGlobalTests(): int {
        $phase4Date = '2024-06-08';
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
            'ascii',
            'currentDay',
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

    private static function getDeleteString(int $length, int $pause): string {
        return str_repeat("§P{$pause}§█§DEL§§DEL§", $length);
    }
}
