<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Developer extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'game_id',
        'name',
        'seniority',
        'busy',
        'project_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'busy' => 'boolean',
    ];

    /**
     * Get the game that owns the developer.
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Get the project that the developer is working on.
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Calculate the progress increment for a project based on developer's seniority
     * Returns a value between 0 and 1
     * 
     * @param int $projectComplexity The complexity of the project
     * @return float The progress increment (between 0 and 1)
     */
    public function calculateProgressIncrement(int $projectComplexity): float
    {
        return ($this->seniority * 0.01) / $projectComplexity;
    }
}