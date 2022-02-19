<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Follow;
use App\Events\FollowCreatedEvent;
use App\Repository\FollowRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class FollowController extends AbstractController
{
    private $followRepo;
    private $eventDispatcher;

    public function __construct(FollowRepository $followRepo, EventDispatcherInterface $eventDispatcher)
    {
        $this->followRepo       = $followRepo;
        $this->eventDispatcher  = $eventDispatcher;
    }

    /**
     * @Route("/{id<\d+>}/f", name="follow", methods={"POST"})
     * @ParamConverter("data", options={"mapping"={"id"="id"}})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function follow(Request $request, User $data): Response
    {
        if (!$this->followRepo->findOneByFollowing($this->getUser()->getId(), $data->getId())) {
        	if ($this->isCsrfTokenValid('follow'.$data->getId(), $request->request->get('_token'))) {
	            $follow = new Follow();
                $follow->setFollower($this->getUser()->getId());
                $data->addFollow($follow);

	            $em = $this->getDoctrine()->getManager();
	            $em->persist($follow);
	            $em->flush();

                $this->eventDispatcher->dispatch(new FollowCreatedEvent($follow));
	        }
        }

        return $this->redirectToRoute('profile', ['id' => $data->getId()]);
    }

    /**
     * @Route("/{id<\d+>}/uf", name="unfollow", methods={"POST"})
     * @ParamConverter("data", options={"mapping"={"id"="id"}})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function unfollow(Request $request, User $data): Response
    {
        if ($this->followRepo->findOneByFollowing($this->getUser()->getId(), $data->getId())) {
            if ($this->isCsrfTokenValid('unfollow'.$data->getId(), $request->request->get('_token'))) {
	            $em = $this->getDoctrine()->getManager();
                $em->remove($this->followRepo->findOneByFollowing($this->getUser()->getId(), $data->getId()));
	            $em->flush();
	        }
        }

        return $this->redirectToRoute('profile', ['id' => $data->getId()]);
    }
}