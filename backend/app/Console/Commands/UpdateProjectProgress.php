<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Project;
use App\Models\Developer;
use App\Events\ProjectStateChanged;
use Illuminate\Support\Facades\Log;

class UpdateProjectProgress extends Command
{
    protected $signature = 'projects:update-progress';
    protected $description = 'Update progress of all active projects';

    protected function calculateAndUpdateProgress(Project $project, Developer $developer): void
    {
        try {
            // Calculate progress increment using developer's method
            $progressIncrement = $developer->calculateProgressIncrement($project->complexity);
            
            Log::info("Updating project {$project->name} progress", [
                'current_progress' => $project->progress,
                'increment' => $progressIncrement,
                'developer' => $developer->name,
                'seniority' => $developer->seniority,
                'complexity' => $project->complexity
            ]);

            // Update project progress using project's method
            $wasCompleted = $project->updateProgress($progressIncrement);

            // Broadcast the project state change
            event(new ProjectStateChanged($project));

            if ($wasCompleted) {
                Log::info("Project {$project->name} completed", [
                    'developer' => $developer->name,
                    'value' => $project->value
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Failed to update project progress", [
                'project' => $project->name,
                'developer' => $developer->name,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function handle()
    {
        try {
            $projects = Project::active()->get();
            
            Log::info("Found {$projects->count()} active projects to update");

            foreach ($projects as $project) {
                $developer = Developer::where('project_id', $project->id)->first();

                if ($developer) {
                    $this->calculateAndUpdateProgress($project, $developer);
                } else {
                    Log::warning("Project {$project->name} is active but has no assigned developer");
                }
            }

            $this->info('Project progress updated successfully.');
        } catch (\Exception $e) {
            Log::error("Failed to update project progress", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            $this->error("Failed to update project progress: " . $e->getMessage());
        }
    }
} 
