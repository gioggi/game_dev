<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class StartWebSocketServer extends Command
{
    protected $signature = 'websockets:start';
    protected $description = 'Start the WebSocket server';

    public function handle()
    {
        $this->info('Starting WebSocket server...');
        $this->call('websockets:serve');
    }
}