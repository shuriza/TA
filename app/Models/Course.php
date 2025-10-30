<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    /** @use HasFactory<\Database\Factories\CourseFactory> */
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'lecturer_id',
        'semester',
        'class',
        'description',
        'color',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    /**
     * Get the lecturer who teaches this course
     */
    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lecturer_id');
    }

    /**
     * Get enrolled students (many-to-many)
     */
    public function students()
    {
        return $this->belongsToMany(User::class, 'course_user')
            ->withTimestamps();
    }

    /**
     * Alias for students relationship
     */
    public function enrolledStudents()
    {
        return $this->students();
    }

    /**
     * Get materials for this course
     */
    public function materials(): HasMany
    {
        return $this->hasMany(Material::class);
    }

    /**
     * Get assignments for this course
     */
    public function assignments(): HasMany
    {
        return $this->hasMany(Assignment::class);
    }

    /**
     * Get LMS mappings for this course
     */
    public function lmsMaps(): HasMany
    {
        return $this->hasMany(LmsMap::class);
    }
}
