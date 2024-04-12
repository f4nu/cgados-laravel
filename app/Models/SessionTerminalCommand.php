<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionTerminalCommand extends Model {
    protected $fillable = ['terminal_session', 'terminal_command_id', 'args'];
}
