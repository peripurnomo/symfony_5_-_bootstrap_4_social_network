<?php

namespace App\EventSubscriber;

use App\Entity\Follow;
use App\Entity\Notification;
use App\Events\FollowCreatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class FollowNotificationSubscriber implements EventSubscriberInterface
{
    private $notification;
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->notification  = new Notification();
    }

    public function onFollowCreated(FollowCreatedEvent $event): void
    {
        $follow = $event->getFollow();

        $this->notification->setType(1); # Tipe 1 digunakan untuk follow.
        $this->notification->setSender($follow->getFollower());
        $this->notification->setReceiver($follow->getUser()->getUsername());

        $this->entityManager->persist($this->notification);
        $this->entityManager->flush();
    }
 
    public static function getSubscribedEvents(): array
    {
        return [
            FollowCreatedEvent::class => [
                'onFollowCreated',
            ]
        ];
    }
}