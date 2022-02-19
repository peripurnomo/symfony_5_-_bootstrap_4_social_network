<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\PostRepository;
use App\Repository\FollowRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ProfileController extends AbstractController
{
    private $postRepo;
    private $followRepo;

    public function __construct(PostRepository $postRepo, FollowRepository $followRepo)
    {
        $this->postRepo   = $postRepo;
        $this->followRepo = $followRepo;
    }

    /**
     * @Route("/profile/{id<\d+>}", name="app_profile", methods={"GET"})
     * @Route("/{id<\d+>}/{page<[1-9]\d*>}", methods={"GET"}, name="post_index_paginated")
     * @ParamConverter("data", options={"mapping"={"id"="id"}})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function profile(Request $request, User $data, int $page = 1): Response
    {
        // dd($this->followRepo->findOneByFollowing($this->getUser()->getId(), $data->getId()));
        
        return $this->render('profile/index.html.twig', [
            'data' => $data,
            'following' => $this->followRepo->findOneByFollowing($this->getUser()->getId(), $data->getId()),
            'paginator' => $this->postRepo->findByUsername($page, $data->getUsername()),
        ]);
    }
}