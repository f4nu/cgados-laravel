<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Directory extends Model
{
    use HasPath;
    
    protected $fillable = ['name', 'parent_id', 'public'];
    
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Directory::class, 'parent_id');
    }
    
    public function children(): HasMany
    {
        return $this->hasMany(Directory::class, 'parent_id');
    }
    
    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }
    
    public function canBeAccessed(): bool {
        return $this->public || SessionData::getSessionData('isRoot', false);
    }
}
