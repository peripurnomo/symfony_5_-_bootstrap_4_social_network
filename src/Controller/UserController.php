<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Type\SearchingType;
use App\Repository\UserRepository;
use App\Form\RegistrationFormType;
use App\Repository\FollowRepository;
use App\Form\Type\ProfilePictureType;
use App\Form\Type\ChangePasswordType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class UserController extends AbstractController
{
    private $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    /**
     * @Route("/setting/menu", name="menu", methods={"GET", "POST"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function menu(Request $request): Response
    {
        return $this->render('user/menu.html.twig');
    }

    /**
     * @Route("/editProfile", name="editProfile", methods={"GET", "POST"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function editProfile(Request $request): Response
    {
        return new Response(
            'Untuk sementara waktu, anda belum dapat mengakses fitur ini.<br>
            Edit profil hanya bisa dilakukan, jika akun anda yang telah terdaftar lebih dari 100 hari.<br>
            Terima kasih :)
        ');

        $user = $this->getUser();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            return $this->redirectToRoute('home');
        }

        return $this->render('user/editProfile.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/changePassword", name="changePassword", methods={"GET", "POST"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function changePassword(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ChangePasswordType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            # Encode the new password.
            $user->setPassword($encoder
                ->encodePassword($user, $form->get('newPassword')
                    ->getData()));

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('Success', "Password anda berhasil diperbarui.");

            return $this->redirectToRoute('profile', [
                'username' => $user->getUsername()
            ]);
        }

        return $this->render('user/changePassword.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    /**
     * @Route("/user/search", name="userSearch")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function search(Request $request)
    {
        $form = $this->createForm(SearchingType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            if ($request->request->get('searching')['search']) {
                $result = $this->userRepo
                    ->findBySearchQuery($request->request
                        ->get('searching')['search']);

                return $this->render('user/searchResult.html.twig', [
                    'results' => $result,
                ]);
            }
        }
        
        return $this->render('user/search.html.twig', [
            'form' =>  $form->createView()
        ]);
    }

    /**
     * @Route("/user/addAvatar", name="addAvatar", methods={"GET", "POST"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function addAvatar(Request $request): Response
    {
        $user = $this->getUser();
        $form = $this->createForm(ProfilePictureType::class);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $image    = $form->get('profilePicture')->getData();
            $fileName = md5(uniqid()).'.'.$image->guessExtension();

            if ($image->getSize() < 10000000){
                $image->move($this->getParameter('avatar_directory'), $fileName);
                $user->setImage($fileName);
                $this->getDoctrine()->getManager()->flush();
            } else {
                $this->addFlash('Success', "Ukuran gambar terlalu besar!");
            }
            
            return $this->redirectToRoute('profile', [
                'username' => $user->getUsername(),
            ]);
        }

        return $this->render('user/addAvatar.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{username<\w+>}/displayAvatar", name="displayAvatar", methods={"GET", "POST"})
     * @ParamConverter("data", options={"mapping"={"username"="username"}})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     */
    public function displayAvatar(Request $request, User $data): Response
    {
        return $this->render('user/displayAvatar.html.twig', [
            'avatar' => $this->userRepo->avatar($data->getUsername())
        ]);
    }

    /**
     * This controller is called directly via the render() function in the twig template.
     */
    public function userSuggestion(Request $request, UserRepository $userRepo): Response
    {
        $suggestion = $userRepo->findByMatching($this->getUser()->getUsername(), $this->getUser()->getCurrentCity());

        return $this->render('user/userSuggestion.html.twig', [
            'suggestion' => $suggestion,
        ]);
     
        /**
         * Kriteria:
            * Profil yang telah melengkapi data,
            * Tinggal di kota yang sama,
            * Beagama yang sama (untuk mengurangi resiko konflik),
            * Profil yang belum di ikuti,
            * Profil yang aktif dalam 30 hari terakhir.

            SELECT *
            FROM `user` u
            LEFT JOIN follow f
            USING(username)
            WHERE u.username != 'peripurnomo'
            AND f.follower != 'peripurnomo'

         * Sample Query :
            SELECT *
            FROM users u
            WHERE u.current_city = 'Bengkulu'
            AND u.religion = 'Islam'
            AND u.status = 'Lajang'
         */
    }
}