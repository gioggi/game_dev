<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Salesperson extends Model
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
        'experience',
        'busy',
        'progress',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'busy' => 'boolean',
        'progress' => 'decimal:2',
    ];

    /**
     * Get the game that owns the salesperson.
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Calculate the progress increment based on experience
     * Returns a value between 0 and 1
     * 
     * @return float The progress increment
     */
    public function calculateProgressIncrement(): float
    {
        return $this->experience * 0.01;
    }

    /**
     * Update progress and handle project generation if needed
     * 
     * @return bool Whether a new project was generated
     */
    public function updateProgress(): bool
    {
        if (!$this->busy) {
            return false;
        }

        $progressIncrement = $this->calculateProgressIncrement();
        $newProgress = min(1.0, $this->progress + $progressIncrement);

        if ($newProgress >= 1.0) {
            $this->generateProject();
            $this->progress = 0;
            return true;
        }

        $this->progress = $newProgress;
        $this->save();
        return false;
    }

    /**
     * Generate a new project for the game
     */
    public function generateProject(): void
    {
        $projectName = 'Project ' . Str::random(8);
        $complexity = min(5, max(1, round($this->experience / 2)));
        $value = $complexity * 1000 + rand(0, 1000);

        Project::create([
            'game_id' => $this->game_id,
            'name' => $projectName,
            'complexity' => $complexity,
            'value' => $value,
            'assigned' => false,
            'progress' => 0,
            'completed' => false,
        ]);

        $this->busy = false;
        $this->save();
    }

    /**
     * Scope a query to only include active salespeople (busy but not completed)
     */
    public function scopeActive($query)
    {
        return $query->where('busy', true);
    }
}