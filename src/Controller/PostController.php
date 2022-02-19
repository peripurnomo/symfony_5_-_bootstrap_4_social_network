<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Post;
use App\Form\PostType;
use App\Entity\Comment;
use App\Form\Type\SearchingType;
use App\Repository\PostRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class PostController extends AbstractController
{
    private $postRepo;

    public function __construct(PostRepository $postRepo)
    {
        $this->postRepo = $postRepo;
    }

    /**
     * @Route("/post/show/{id<\d+>}", name="postShow", methods={"GET"})
     * @ParamConverter("post", options={"mapping"={"id"="id"}})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function postShow(Request $request, Post $post): Response
    {
        return $this->render('post/show.html.twig', [
            'post' => $post
        ]);
    }

    /**
     * @Route("/post/new", name="postNew", methods={"GET", "POST"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function postNew(Request $request): Response
    {
        $post = new Post();
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $post->setUsername($this->getUser()->getUsername());
            $em = $this->getDoctrine()->getManager();
            $em->persist($post);
            $em->flush();
            
            $this->addFlash('Success', 'Post published!');
            return $this->redirectToRoute('home');
        }
        
        return $this->render('post/form/new.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/post/edit/{id<\d+>}", name="postEdit", methods={"GET", "POST"})
     * @ParamConverter("post", options={"mapping"={"id"="id"}})
     * @Security("user.getUsername() == post.getUsername()")
     */
    public function postEdit(Request $request, Post $post): Response
    {
        $form = $this->createForm(PostType::class, $post);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            
            return $this->redirectToRoute('profile', [
                'username' => $this->getUser()->getUsername()
            ]);
        }

        return $this->render('post/form/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/post/delete/{id}", name="postDelete", methods={"DELETE"})
     * @ParamConverter("post", options={"mapping"={"id"="id"}})
     * @Security("user.getUsername() == post.getUsername()")
     */
    public function postDelete(Request $request, Post $post): Response
    {
        if ($this->isCsrfTokenValid('delete'.$post->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($post);
            $em->flush();
        }
        
        return $this->redirectToRoute('profile', [
            'username' => $this->getUser()->getUsername()
        ]);
    }

    /**
     * @Route("/{username<\w+>}/totalPost", name="totalPost", methods={"GET"})
     * @ParamConverter("data", options={"mapping"={"username"="username"}})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function totalPost(Request $request, User $data): Response
    {
        return $this->render('post/totalPost.html.twig', [
            # Param username total follower.
            'post' => $this->postRepo->count([
                'username' => $data->getUsername()
            ])
        ]);
    }
    
    /**
     * @Route("/post/search", name="postSearch")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function postSearch(Request $request)
    {
        $form = $this->createForm(SearchingType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($request->request->get('searching')['search']) {
                $result = $this->postRepo
                    ->findBySearchQuery($request->request
                        ->get('searching')['search']);

                return $this->render('post/searchResult.html.twig', [
                    'results' => $result,
                ]);
            }
        }
        
        return $this->render('post/search.html.twig', [
            'form' =>  $form->createView()
        ]);
    }
}