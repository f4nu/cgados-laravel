<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class File extends Model
{
    use HasPath;
    
    protected $fillable = [
        'name',
        'content',
        'directory_id',
        'public',
    ];
    
    public function directory(): BelongsTo
    {
        return $this->belongsTo(Directory::class);
    }
    
    public function canBeAccessed(): bool {
        return $this->public || SessionData::getSessionData('isRoot', false);
    }
}
