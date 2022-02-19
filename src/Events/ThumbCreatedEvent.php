<?php

namespace App\Events;

use App\Entity\Thumb;
use Symfony\Contracts\EventDispatcher\Event;

class ThumbCreatedEvent extends Event
{
    protected $thumb;

    public function __construct(Thumb $thumb)
    {
        $this->thumb = $thumb;
    }

    public function getThumb(): Thumb
    {
        return $this->thumb;
    }
}