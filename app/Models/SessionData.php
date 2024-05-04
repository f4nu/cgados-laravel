<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionData extends Model
{
    use HasFactory;
    protected $fillable = ['terminal_session', 'data'];

    public static function getFromTerminalSession(): self {
        $terminalSession = request()->session()->get('terminal_session');
        return self::firstOrCreate(
            [
                'terminal_session' => $terminalSession,
            ]
        );
    }

    public static function getGlobalSession(): self {
        return self::firstOrCreate(
            [
                'terminal_session' => 'fanu_global_session-81823791231m',
            ]
        );
    }

    // This function saves data in the json field. The data is passed as a key with dot notation and the value must be saved in the json field.
    // For example, if the key is 'foo.bar' and the value is 'baz', the json field should be {"foo": {"bar": "baz"}}
    public function saveData(string $key, $value): void {
        $data = json_decode($this->data, true);
        $keys = explode('.', $key);
        $lastKey = array_pop($keys);
        $currentData = &$data;
        foreach ($keys as $key) {
            if (!isset($currentData[$key])) {
                $currentData[$key] = [];
            }
            $currentData = &$currentData[$key];
        }
        $currentData[$lastKey] = $value;
        $this->data = json_encode($data);
        $this->save();
    }

    // This function retrieves data from the json field. The data is passed as a key with dot notation and the value must be retrieved from the json field.
    // For example, if the key is 'foo.bar', the json field is {"foo": {"bar": "baz"}}, and the return value should be 'baz'
    // If the value is not found, the function must return null
    public function getData(string $key) {
        $data = json_decode($this->data, true);
        $keys = explode('.', $key);
        $currentData = $data;
        foreach ($keys as $key) {
            if (!isset($currentData[$key])) {
                return null;
            }
            $currentData = $currentData[$key];
        }
        return $currentData;
    }

}
