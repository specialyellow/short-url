<?php

namespace SpecialYellow\ShortURL\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Livewire\Livewire;
use SpecialYellow\ShortURL\Models\ShortURL;
use SpecialYellow\ShortURL\Models\ShortURLVisit;

class ShortURLVisited
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The short URL that was visited.
     *
     * @var ShortURL
     */
    public $shortURL;

    /**
     * Details of the visitor that visited the short URL.
     *
     * @var ShortURLVisit
     */
    public $shortURLVisit;

    /**
     * Create a new event instance.
     *
     * @param  ShortURL  $shortURL
     * @param  ShortURLVisit  $shortURLVisit
     */
    public function __construct(ShortURL $shortURL, ShortURLVisit $shortURLVisit)
    {
        $this->shortURL = $shortURL;
        $this->shortURLVisit = $shortURLVisit;
    }
}
