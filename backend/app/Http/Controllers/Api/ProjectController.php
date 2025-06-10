<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Events\ProjectStateChanged;
use App\Jobs\AdvanceProjectProgress;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProjectController extends Controller
{
    /**
     * Display a listing of the projects for a game.
     */
    public function index(Request $request): JsonResponse
    {
        $gameId = $request->query('game_id');
        $projects = Project::where('game_id', $gameId)->get();
        return response()->json($projects);
    }

    /**
     * Store a newly created project.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'game_id' => 'required|exists:games,id',
            'name' => 'required|string|max:255',
            'complexity' => 'required|integer|min:1',
            'value' => 'required|numeric|min:0',
            'assigned' => 'required|boolean',
            'progress' => 'required|numeric|min:0',
            'completed' => 'required|boolean',
        ]);

        try {
            return \DB::transaction(function () use ($request) {
                $game = \App\Models\Game::lockForUpdate()->findOrFail($request->game_id);
                $pointsToDeduct = $request->value * 0.1;

                if ($game->money < $pointsToDeduct) {
                    return response()->json(['error' => 'Not enough money to create project'], 400);
                }

                $game->money = $game->money - $pointsToDeduct;
                $game->save();

                $project = Project::create($request->all());
                AdvanceProjectProgress::dispatch($project);

                return response()->json($project, 201);
            });
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to create project: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified project.
     */
    public function show(Project $project): JsonResponse
    {
        return response()->json($project);
    }

    /**
     * Update the specified project.
     */
    public function update(Request $request, Project $project): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'complexity' => 'sometimes|integer|min:1',
            'value' => 'sometimes|numeric|min:0',
            'assigned' => 'sometimes|boolean',
            'progress' => 'sometimes|numeric|min:0',
            'completed' => 'sometimes|boolean',
        ]);

        $project->update($request->all());

        // Broadcast the project state change
        event(new ProjectStateChanged($project));

        return response()->json($project);
    }

    /**
     * Assign a developer to a project.
     */
    public function assignDeveloper(Request $request, Project $project): JsonResponse
    {
        $request->validate([
            'developer_id' => 'required|exists:developers,id'
        ]);

        try {
            return \DB::transaction(function () use ($request, $project) {
                $developer = \App\Models\Developer::findOrFail($request->developer_id);

                if ($developer->busy) {
                    return response()->json(['error' => 'Developer is already assigned to a project'], 400);
                }

                if ($project->assigned) {
                    return response()->json(['error' => 'Project is already assigned to a developer'], 400);
                }

                if ($developer->game_id !== $project->game_id) {
                    return response()->json(['error' => 'Developer does not belong to the same game'], 400);
                }

                $developer->busy = true;
                $developer->project_id = $project->id;
                $developer->save();
                $developer->load('project');

                $project->assigned = true;
                $project->progress = 0;
                $project->completed = false;
                $project->save();

                event(new ProjectStateChanged($project));

                return response()->json([
                    'project' => $project,
                    'developer' => $developer
                ]);
            });
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to assign developer: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Remove the specified project.
     */
    public function destroy(Project $project): JsonResponse
    {
        $project->delete();
        return response()->json(null, 204);
    }
}
