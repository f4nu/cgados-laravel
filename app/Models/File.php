<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class File extends Model
{
    use HasPath;
    
    public function directory(): BelongsTo
    {
        return $this->belongsTo(Directory::class);
    }
}
