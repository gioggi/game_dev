<?php

namespace App\Events;

use App\Models\Salesperson;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class SalespersonProgressChanged
{
    use Dispatchable, SerializesModels;

    /**
     * The salesperson instance.
     *
     * @var \App\Models\Salesperson
     */
    public $salesperson;

    /**
     * Create a new event instance.
     *
     * @param  \App\Models\Salesperson  $salesperson
     * @return void
     */
    public function __construct(Salesperson $salesperson)
    {
        $this->salesperson = $salesperson;
    }
}