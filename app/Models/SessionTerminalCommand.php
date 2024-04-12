<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionTerminalCommand extends Model {
    protected $fillable = ['ip', 'terminal_command_id', 'args'];
}
