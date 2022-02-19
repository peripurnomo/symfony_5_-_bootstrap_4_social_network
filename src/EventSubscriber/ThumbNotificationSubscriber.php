<?php

namespace App\EventSubscriber;

use App\Entity\Thumb;
use App\Entity\Notification;
use App\Events\ThumbCreatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ThumbNotificationSubscriber implements EventSubscriberInterface
{
    private $notification;
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->notification  = new Notification();
    }

    public function onThumbUpCreated(ThumbCreatedEvent $event): void
    {
        $thumbUp = $event->getThumb();
        
        $this->notification->setType(3); # Tipe 3 digunakan untuk like reaction.
        $this->notification->setSender($thumbUp->getLiker());
        $this->notification->setReceiver($thumbUp->getPostOwner());
        $this->notification->setRefid($thumbUp->getPost()->getId());
        $this->notification->setSummary($thumbUp->getPost()->getBody());

        $this->entityManager->persist($this->notification);
        $this->entityManager->flush();
    }
 
    public static function getSubscribedEvents(): array
    {
        return [
            ThumbCreatedEvent::class => [
                'onThumbUpCreated',
            ]
        ];
    }
}