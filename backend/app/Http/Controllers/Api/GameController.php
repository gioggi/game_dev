<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Developer;
use App\Models\Game;
use App\Models\Salesperson;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class GameController extends Controller
{
    /**
     * Get all games for a session.
     */
    public function index(Request $request): JsonResponse
    {
        $sessionId = $request->query('session_id');

        if ($sessionId) {
            $games = Game::where('session_id', $sessionId)->get();
        } else {
            $games = Game::all();
        }

        return response()->json($games);
    }

    /**
     * Start a new game.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'session_id' => 'nullable|string',
        ]);

        $game = Game::create([
            'name' => $request->name,
            'money' => 5000.00,
            'session_id' => $request->session_id,
        ]);

        $developer = Developer::create([
            'game_id' => $game->id,
            'name' => 'Initial Developer',
            'seniority' => 1,
            'busy' => false,
        ]);

        $salesperson = Salesperson::create([
            'game_id' => $game->id,
            'name' => 'Initial Salesperson',
            'experience' => 1,
            'busy' => false,
            'progress' => 0,
        ]);

        $game->load(['developers', 'salespeople', 'projects']);

        return response()->json($game, 201);
    }

    /**
     * Get a specific game with its relationships.
     */
    public function show(Game $game): JsonResponse
    {
        $game->load(['developers.project', 'salespeople', 'projects']);
        return response()->json($game);
    }

    /**
     * Update a game's state.
     */
    public function update(Request $request, Game $game): JsonResponse
    {
        $request->validate([
            'money' => 'sometimes|numeric|min:0',
        ]);

        $game->update($request->only(['money']));
        return response()->json($game);
    }

    /**
     * Delete a game.
     */
    public function destroy(Game $game): JsonResponse
    {
        $game->delete();
        return response()->json(null, 204);
    }

    /**
     * Delete all games for a session.
     */
    public function destroyBySession(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
        ]);

        $count = Game::where('session_id', $request->session_id)->delete();

        return response()->json(['deleted' => $count], 200);
    }
}
