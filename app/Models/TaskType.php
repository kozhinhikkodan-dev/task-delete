<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskType extends Model
{
    use HasFactory;

    const PUBLISHABLE_TASK_TYPES = [
        'Graphic Design',
        'Video Editing'	
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'code',
        'description',
        'base_rate',
        'estimated_time_minutes',
        'priority',
        'status',
        'requirements',
        'meta_data',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'base_rate' => 'decimal:2',
        'estimated_time_minutes' => 'integer',
        'requirements' => 'array',
        'meta_data' => 'array',
    ];

    /**
     * Get the priority options.
     */
    public static function getPriorityOptions(): array
    {
        return [
            'low' => 'Low',
            'medium' => 'Medium',
            'high' => 'High',
        ];
    }

    /**
     * Get the status options.
     */
    public static function getStatusOptions(): array
    {
        return [
            'active' => 'Active',
            'inactive' => 'Inactive',
        ];
    }

    /**
     * Scope a query to only include active task types.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include inactive task types.
     */
    public function scopeInactive($query)
    {
        return $query->where('status', 'inactive');
    }

    /**
     * Get the formatted base rate.
     */
    public function getFormattedBaseRateAttribute(): string
    {
        return '$' . number_format($this->base_rate, 2);
    }

    /**
     * Get the formatted estimated time.
     */
    public function getFormattedEstimatedTimeAttribute(): string
    {
        $hours = floor($this->estimated_time_minutes / 60);
        $minutes = $this->estimated_time_minutes % 60;
        
        if ($hours > 0) {
            return $hours . 'h ' . $minutes . 'm';
        }
        
        return $minutes . 'm';
    }

    /**
     * Get the priority label.
     */
    public function getPriorityLabelAttribute(): string
    {
        return self::getPriorityOptions()[$this->priority] ?? $this->priority;
    }

    /**
     * Get the status label.
     */
    public function getStatusLabelAttribute(): string
    {
        return self::getStatusOptions()[$this->status] ?? $this->status;
    }

    /**
     * Get the tasks for the task type.
     */
    public function tasks()
    {
        return $this->hasMany(Task::class);
    }
}
