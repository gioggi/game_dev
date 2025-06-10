<?php

namespace App\Jobs;

use App\Models\Project;
use App\Events\ProjectStateChanged;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class AdvanceProjectProgress implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The project instance.
     *
     * @var \App\Models\Project
     */
    protected $project;

    /**
     * Create a new job instance.
     *
     * @param  \App\Models\Project  $project
     * @return void
     */
    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // Advance the project progress
        $progressIncrement = 10 / $this->project->complexity; // Increment based on complexity
        $this->project->progress = min(100, $this->project->progress + $progressIncrement);
        
        // Save the project
        $this->project->save();
        
        // Broadcast the project state change
        event(new ProjectStateChanged($this->project));
    }
}