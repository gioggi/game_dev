<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Project extends Model
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
        'complexity',
        'value',
        'assigned',
        'progress',
        'completed',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'value' => 'decimal:2',
        'assigned' => 'boolean',
        'progress' => 'decimal:2',
        'completed' => 'boolean',
    ];

    /**
     * Get the game that owns the project.
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Get the developer working on this project.
     */
    public function developer(): HasOne
    {
        return $this->hasOne(Developer::class);
    }

    /**
     * Update project progress and handle completion
     * Progress is stored as a decimal between 0 and 1
     * 
     * @param float $progressIncrement The progress increment to add (between 0 and 1)
     * @return bool Whether the project was completed
     */
    public function updateProgress(float $progressIncrement): bool
    {
        try {
            if ($this->completed) {
                return true;
            }

            $oldProgress = (float)$this->progress;
            $newProgress = min(0.99, $oldProgress + $progressIncrement);

            if ($newProgress >= 0.99) {
                $this->complete();
                return true;
            }

            $this->progress = $newProgress;
            $this->save();
            return false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * Complete the project and handle rewards
     */
    public function complete(): void
    {
        try {
            DB::beginTransaction();
            
            $this->progress = 1.0;
            $this->completed = true;
            $this->assigned = false;
            
            $this->game->money += $this->value;
            $this->game->save();

            if ($developer = $this->developer) {
                $developer->busy = false;
                $developer->project_id = null;
                $developer->save();
            }

            $this->save();
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * Generate a new project for a game
     * 
     * @param int $gameId The game ID
     * @param int $salespersonExperience The experience of the salesperson generating the project
     * @return Project The newly created project
     */
    public static function generateNew(int $gameId, int $salespersonExperience): self
    {
        // Generate a random project name
        $projectName = 'Project ' . Str::random(8);

        // Generate random complexity between 1 and 5 based on salesperson experience
        $complexity = min(5, max(1, round($salespersonExperience / 2)));

        // Generate random value based on complexity
        $value = $complexity * 1000 + rand(0, 1000);

        // Create and return the project
        return self::create([
            'game_id' => $gameId,
            'name' => $projectName,
            'complexity' => $complexity,
            'value' => $value,
            'assigned' => false,
            'progress' => 0,
            'completed' => false,
        ]);
    }

    /**
     * Scope a query to only include active projects (assigned but not completed)
     */
    public function scopeActive($query)
    {
        return $query->where('assigned', true)
                    ->where('completed', false);
    }
}