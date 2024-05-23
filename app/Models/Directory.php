<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Directory extends Model
{
    protected $fillable = ['name', 'parent_id'];
    
    public function parent(): BelongsTo
    {
        return $this->belongsTo(Directory::class, 'parent_id');
    }
    
    public function children(): HasMany
    {
        return $this->hasMany(Directory::class, 'parent_id');
    }
}
