<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'money',
        'session_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'money' => 'decimal:2',
    ];

    /**
     * Get the developers for the game.
     */
    public function developers(): HasMany
    {
        return $this->hasMany(Developer::class);
    }

    /**
     * Get the salespeople for the game.
     */
    public function salespeople(): HasMany
    {
        return $this->hasMany(Salesperson::class);
    }

    /**
     * Get the projects for the game.
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
