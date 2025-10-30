<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Assignment extends Model
{
    /** @use HasFactory<\Database\Factories\AssignmentFactory> */
    use HasFactory;

    protected $fillable = [
        'course_id',
        'title',
        'description',
        'due_at',
        'status',
        'priority',
        'effort_mins',
        'impact',
        'tag',
        'lms_url',
        'allow_late_submission',
        'max_score',
        'attachments',
    ];

    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'allow_late_submission' => 'boolean',
            'attachments' => 'array',
        ];
    }

    /**
     * Get the course this assignment belongs to
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    /**
     * Get submissions for this assignment
     */
    public function submissions(): HasMany
    {
        return $this->hasMany(Submission::class);
    }

    /**
     * Get reminders for this assignment
     */
    public function reminders(): HasMany
    {
        return $this->hasMany(Reminder::class);
    }

    /**
     * Get files attached to this assignment
     */
    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    /**
     * Get LMS mappings for this assignment
     */
    public function lmsMaps(): HasMany
    {
        return $this->hasMany(LmsMap::class);
    }
}
