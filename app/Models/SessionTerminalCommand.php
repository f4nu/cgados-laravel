<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class SessionTerminalCommand extends Model {
    protected $fillable = ['terminal_session', 'terminal_command_id', 'args'];
    
    public static function activeTerminalSessionsCount(): int {
        return self::activeTerminalSessions()
            ->count();
    }
    
    public static function activeTerminalSessions(): Collection
    {
        return self::query()
            ->where('created_at', '>', now()->subMinutes(5))
            ->get()
            ->unique('terminal_session');
    }
}
