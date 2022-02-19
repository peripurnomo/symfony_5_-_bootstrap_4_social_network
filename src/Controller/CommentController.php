<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Post;
use App\Entity\Comment;
use App\Form\CommentType;
use App\Events\CommentCreatedEvent;
use App\Repository\CommentRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class CommentController extends AbstractController
{
    private $commentRepo;
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher, CommentRepository $commentRepo)
    {
        $this->commentRepo     = $commentRepo;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @Route("/comment/new/{id<\d+>}", name="commentNew", methods={"POST"})
     * @ParamConverter("post", options={ "mapping"={"id"="id"} })
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function commentNew(Request $request, Post $post): Response
    {
        $comment = new Comment();
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $comment->setUsername($post->getUsername());
            $comment->setCommenter($this->getUser()->getUsername());
            # Save relation.
            $post->addComment($comment);
            
            # Abaikan event untuk komentar sendiri.
            if ($comment->getCommenter() !== $comment->getUsername()) {
                $this->eventDispatcher->dispatch(new CommentCreatedEvent($comment));
            }
            
            $em = $this->getDoctrine()->getManager();
            $em->persist($comment);
            $em->flush();
        }
        
        return $this->redirectToRoute('postShow', [
            'id' => $post->getId()
        ]);
    }

    /**
     * @Route("/comment/edit/{id<\d+>}", name="commentEdit", methods={"GET", "POST"})
     * @ParamConverter("comment", options={ "mapping"={"id"="id"} })
     * @Security("user.getusername() == comment.getCommenter()")
     */
    public function commentEdit(Request $request, Comment $comment): Response
    {
        $form = $this->createForm(CommentType::class, $comment);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();            
            return $this->redirectToRoute('postShow', [
                'id' => $comment->getPost()->getId()
            ]);
        }

        return $this->render('comment/form/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/comment/delete/{id}", name="commentDelete", methods={"DELETE"})
     * @ParamConverter("comment", options={ "mapping"={"id"="id"} })
     * @Security("user.getusername() == comment.getCommenter()")
     */
    public function commentDelete(Request $request, Comment $comment): Response
    {
        # Check csrf token.
        if ($this->isCsrfTokenValid('delete'.$comment->getId(), $request->request->get('_token'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($comment);
            $em->flush();            
        }

        return $this->redirectToRoute('postShow', [
            'id' => $comment->getPost()->getId()
        ]);
    }

    /**
     * @Route("/comment/show/{id<\d+>}", name="commentShow", methods={"GET"})
     * @ParamConverter("post", options={ "mapping"={"id"="id"} })
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function commentShow(Request $request, Post $post): Response
    {
        return $this->render('comment/show.html.twig', [
            'post'      => $post,
            'comments'  => $this->commentRepo->findByPostId($post->getId()), # Comment per post.
        ]);
    }

    public function newCommentForm(Post $post)
    {
        $form = $this->createForm(CommentType::class, null, [
            'action' => $this->generateUrl('commentNew', [
                'id' => $post->getId()
            ]),'method' => 'POST'
        ]);

        return $this->render('comment/form/new.html.twig', [
            'post' => $post,
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/comment/totalComment/{id<\d+>}", name="totalComment", methods={"GET"})
     * @ParamConverter("post", options={"mapping"={"id"="id"}})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function totalComment(Request $request, Post $post): Response
    {
        return $this->render('comment/totalComment.html.twig', [
            # Total comment per post.
            'comment' => $this->commentRepo->count([
                'post' => $post->getId()
            ])
        ]);
    }
}