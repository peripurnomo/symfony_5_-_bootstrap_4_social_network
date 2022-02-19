<?php

namespace App\Events;

use App\Entity\Follow;
use Symfony\Contracts\EventDispatcher\Event;

class FollowCreatedEvent extends Event
{
    protected $follow;

    public function __construct(Follow $follow)
    {
        $this->follow = $follow;
    }

    public function getFollow(): Follow
    {
        return $this->follow;
    }
}