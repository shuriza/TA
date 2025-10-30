<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LmsMap extends Model
{
    /** @use HasFactory<\Database\Factories\LmsMapFactory> */
    use HasFactory;

    protected $fillable = [
        'mappable_type',
        'mappable_id',
        'lms_platform',
        'external_id',
        'external_url',
        'last_synced_at',
    ];

    protected function casts(): array
    {
        return [
            'last_synced_at' => 'datetime',
        ];
    }

    /**
     * Get the parent mappable model (Course or Assignment)
     */
    public function mappable()
    {
        return $this->morphTo();
    }
}
