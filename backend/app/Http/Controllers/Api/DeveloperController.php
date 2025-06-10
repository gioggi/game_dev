<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Developer;
use App\Models\Game;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class DeveloperController extends Controller
{
    /**
     * Display a listing of the developers for a game.
     */
    public function index(Request $request): JsonResponse
    {
        $gameId = $request->query('game_id');
        $developers = Developer::where('game_id', $gameId)
            ->with('project')
            ->get();
        return response()->json($developers);
    }

    /**
     * Store a newly created developer.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'game_id' => 'required|exists:games,id',
            'name' => 'required|string|max:255',
            'seniority' => 'required|integer|min:1',
            'busy' => 'required|boolean',
            'cost' => 'required|numeric|min:0',
        ]);

        try {
            return \DB::transaction(function () use ($request) {
                $game = \App\Models\Game::lockForUpdate()->findOrFail($request->game_id);

                if ($game->money < $request->cost) {
                    return response()->json(['error' => 'Not enough money to hire developer'], 400);
                }

                $game->money -= $request->cost;
                $game->save();

                $developer = Developer::create([
                    'game_id' => $request->game_id,
                    'name' => $request->name,
                    'seniority' => $request->seniority,
                    'busy' => $request->busy,
                ]);

                return response()->json([
                    'developer' => $developer,
                    'remaining_money' => $game->money
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to hire developer: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified developer.
     */
    public function show(Developer $developer): JsonResponse
    {
        $developer->load('project');
        return response()->json($developer);
    }

    /**
     * Update the specified developer.
     */
    public function update(Request $request, Developer $developer): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'seniority' => 'sometimes|integer|min:1',
            'busy' => 'sometimes|boolean',
            'project_id' => 'sometimes|exists:projects,id',
        ]);

        $developer->update($request->all());
        $developer->load('project');
        return response()->json($developer);
    }

    /**
     * Remove the specified developer.
     */
    public function destroy(Developer $developer): JsonResponse
    {
        $developer->delete();
        return response()->json(null, 204);
    }
} 