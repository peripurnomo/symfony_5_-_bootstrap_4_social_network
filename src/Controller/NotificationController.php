<?php

namespace App\Controller;

use App\Entity\Notification;
use App\Events\CommentCreatedEvent;
use App\Repository\PostRepository;
use App\Repository\NotificationRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class NotificationController extends AbstractController
{
    private $notificationRepo;

    public function __construct(NotificationRepository $notificationRepo)
    {
        $this->notificationRepo = $notificationRepo;
    }

    /**
     * @Route("/notification", name="notification", methods={"GET"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function index(Request $request): Response
    {
        return $this->render('notification/index.html.twig', [
            'notifications' => $this->notificationRepo
                ->findByUsername($this->getUser()->getUsername())
        ]);
    }

    /**
     * @Route("/notification/new", name="totalNotification", methods={"GET"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function totalNotification(Request $request): Response
    {
        return $this->render('notification/totalNotification.html.twig', [
            'notification' => $this->notificationRepo->count([
                'status' => null,
                'receiver' => $this->getUser()->getUsername(),
            ])
        ]);
    }
}