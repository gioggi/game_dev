<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Salesperson;
use App\Events\SalespersonProgressChanged;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class SalespersonController extends Controller
{
    /**
     * Display a listing of the salespeople for a game.
     */
    public function index(Request $request): JsonResponse
    {
        $gameId = $request->query('game_id');
        if (!$gameId) {
            return response()->json(['error' => 'Game ID is required'], 400);
        }

        $salespeople = Salesperson::where('game_id', $gameId)
            ->select(['id', 'name', 'experience', 'busy', 'progress', 'game_id'])
            ->get();

        return response()->json($salespeople);
    }

    /**
     * Store a newly created salesperson.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'game_id' => 'required|exists:games,id',
            'name' => 'required|string|max:255',
            'experience' => 'required|integer|min:1',
            'progress' => 'required|numeric|min:0',
            'cost' => 'required|numeric|min:0',
        ]);

        try {
            return \DB::transaction(function () use ($request) {
                $game = \App\Models\Game::lockForUpdate()->findOrFail($request->game_id);

                if ($game->money < $request->cost) {
                    return response()->json(['error' => 'Not enough money to hire salesperson'], 400);
                }

                $game->money -= $request->cost;
                $game->save();

                $salesperson = Salesperson::create([
                    'game_id' => $request->game_id,
                    'name' => $request->name,
                    'experience' => $request->experience,
                    'busy' => false,
                    'progress' => $request->progress,
                ]);

                return response()->json([
                    'salesperson' => $salesperson,
                    'remaining_money' => $game->money
                ], 201);
            });
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to hire salesperson: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified salesperson.
     */
    public function show(Salesperson $salesperson): JsonResponse
    {
        return response()->json($salesperson);
    }

    /**
     * Update the specified salesperson.
     */
    public function update(Request $request, Salesperson $salesperson): JsonResponse
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'experience' => 'sometimes|integer|min:1',
            'busy' => 'sometimes|boolean',
            'progress' => 'sometimes|numeric|min:0|max:1',
        ]);

        $salesperson->update($request->all());

        return response()->json($salesperson);
    }

    /**
     * Remove the specified salesperson.
     */
    public function destroy(Salesperson $salesperson): JsonResponse
    {
        $salesperson->delete();
        return response()->json(null, 204);
    }
}
