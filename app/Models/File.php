<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class File extends Model
{
    /** @use HasFactory<\Database\Factories\FileFactory> */
    use HasFactory;

    protected $fillable = [
        'fileable_type',
        'fileable_id',
        'filename',
        'original_name',
        'path',
        'mime_type',
        'size',
    ];

    /**
     * Get the parent fileable model (Assignment or Submission)
     */
    public function fileable()
    {
        return $this->morphTo();
    }
}
