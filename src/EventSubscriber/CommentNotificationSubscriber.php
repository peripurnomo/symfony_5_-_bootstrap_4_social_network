<?php

namespace App\EventSubscriber;

use App\Entity\Comment;
use App\Entity\Notification;
use App\Events\CommentCreatedEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class CommentNotificationSubscriber implements EventSubscriberInterface
{
    private $notification;
    private $entityManager;
    private $notificationType;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->notificationType = 2;
        $this->entityManager    = $entityManager;
        $this->notification     = new Notification();
    }

    public function onCommentCreated(CommentCreatedEvent $event): void
    {
        $comment = $event->getComment();

        $this->notification->setType($this->notificationType);
        $this->notification->setSender($comment->getCommenter());
        $this->notification->setReceiver($comment->getUsername());
        $this->notification->setRefId($comment->getPost()->getId());
        $this->notification->setSummary($comment->getPost()->getBody());

        $this->entityManager->persist($this->notification);
        $this->entityManager->flush();
    }
 
    public static function getSubscribedEvents(): array
    {
        return [
            CommentCreatedEvent::class => [
                'onCommentCreated',
            ]
        ];
    }
}