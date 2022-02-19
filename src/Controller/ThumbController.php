<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Thumb;
use App\Events\ThumbCreatedEvent;
use App\Repository\ThumbRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ThumbController extends AbstractController
{
	private $thumbRepo;
    private $eventDispatcher;

	public function __construct(ThumbRepository $thumbRepo, EventDispatcherInterface $eventDispatcher)
	{
		$this->thumbRepo = $thumbRepo;
        $this->eventDispatcher = $eventDispatcher;
	}

    /**
     * @Route("/thumbUp/{id}", name="app_like", methods={"POST"})
     * @ParamConverter("data", options={"mapping"={"id"="id"}})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function thumbUp(Request $request, Post $data): Response
    {
    	# Check for liking.
        if (!$this->thumbRepo->findOneByLiking($this->getUser()->getUsername(), $data->getId())){
	    	# Check csrf token.
            if ($this->isCsrfTokenValid('like'.$data->getId(), $request->request->get('_token'))) {
		    	$thumbUp = new Thumb();
                
		        $thumbUp->setPostOwner($data->getUsername());
                $thumbUp->setLiker($this->getUser()->getUsername());
                $data->addThumb($thumbUp); # Save relation.

                # Ignore event untuk liking post sendiri.
                if ($thumbUp->getLiker() !== $thumbUp->getPostOwner()) {
                    $this->eventDispatcher->dispatch(new ThumbCreatedEvent($thumbUp));
                }
                
		        $em = $this->getDoctrine()->getManager();
		        $em->persist($thumbUp);
		        $em->flush();
	    	}
	    }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/unlike/{id}", name="app_unlike", methods={"POST"})
     * @ParamConverter("data", options={"mapping"={"id"="id"}})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function thumbDown(Request $request, Post $data): Response
    {
        # Make sure for liking.
        if ($this->thumbRepo->findOneByLiking($this->getUser()->getUsername(), $data->getId())){
            # Check csrf.
            if ($this->isCsrfTokenValid('unlike'.$data->getId(), $request->request->get('_token'))) {
                $em = $this->getDoctrine()->getManager();
                # Remove object.
                $em->remove($this->thumbRepo
                    ->findOneByLiking($this->getUser()->getUsername(),
                        $data->getId()));
                $em->flush();
            }
        }

        return $this->redirect($request->headers->get('referer'));
        // return $this->redirect($request->server->getHeaders()['REFERER']);
    }

    /**
     * @Route("/totalLiking/{id}", name="totalLiking", methods={"GET"})
     * @ParamConverter("data", options={"mapping"={"id"="id"}})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function totalLiking(Request $request, Post $data): Response
    {
        return $this->render('thumb/totalLiking.html.twig', [
            # Total liking per post.
            'liking' => $this->thumbRepo->countLike($data->getId())
        ]);
    }
}