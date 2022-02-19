<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Password;
use App\Form\Type\SubmitEmailType;
use App\Repository\UserRepository;
use App\Form\Type\ResetPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserAuthenticatorController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * @Route("/login", name="app_login", methods={"GET", "POST"})
     */
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        if ($this->getUser()) {
            return $this->redirectToRoute('home');
        }

        return $this->render('security/login.html.twig', [
            'last_username' => $authenticationUtils->getLastUsername(),
            'error'         => $authenticationUtils->getLastAuthenticationError()
        ]);
    }

    /**
     * @Route("/logout", name="app_logout", methods={"GET"})
     */
    public function logout() {}

    /**
     * @Route("/forgot-password", name="forgotPassword", methods={"GET", "POST"})
     */
    public function forgotPassword(Request $request): Response
    {
        $email = new Password();
        $form = $this->createForm(SubmitEmailType::class, $email);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            # Check e-mail - one email one token
            if ($this->entityManager->getRepository(User::class)->findOneByEmail($request->request->get('submit_email')['email'])) {
                # Check csrf token.
                if (!$this->entityManager->getRepository(Password::class)->findOneByEmail($request->request->get('submit_email')['email'])) {
                    $email->setEmail($request->request->get('submit_email')['email']); # Set email
                    $email->setToken(md5(uniqid())); # Set token.

                    $em = $this->getDoctrine()->getManager();
                    $em->persist($email); # Save email and token.
                    $em->flush();

                    $this->addFlash('Success', 'Perikasa e-mail anda!');
                    return $this->redirectToRoute('app_login');
                }
            }
        }

        return $this->render('security/forgotPassword.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/reset-password/{token<\w+>}", name="resetPassword", methods={"GET", "POST"})
     * @ParamConverter("token", options={"mapping"={"token"="token"}})
     */
    public function resetPassword(Request $request, Password $data, UserPasswordEncoderInterface $encoder): Response
    {
        $user = $this->entityManager->getRepository(User::class)->findOneByEmail($data->getEmail());
        $form = $this->createForm(ResetPasswordType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            # Encode the new password.
            $user->setPassword($encoder
                ->encodePassword($user, $form->get('newPassword')
                    ->getData()));

            $em = $this->getDoctrine()->getManager();
            $em->persist($user); # Save encoded password.
            $em->remove($data); # Remove token.
            $em->flush();
            
            $this->addFlash('Success', "Password berhasil di atur ulang!");
            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/resetPassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}