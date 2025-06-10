<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

// Public channel for projects
Broadcast::channel('projects', function () {
    return true;
});

// Public channel for salespeople
Broadcast::channel('salespeople', function () {
    return true;
});

Broadcast::channel('game.{gameId}', function ($user, $gameId) {
    // For now, allow any authenticated user to join the game channel.
    // In a real application, you'd verify if the user belongs to this game.
    if ($user) {
        return ['id' => $user->id, 'name' => $user->name];
    }
    return false;
});
