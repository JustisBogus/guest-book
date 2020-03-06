<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class RegisterController extends AbstractController
{
    /**
     * @var RouterInterface
     */
    private $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @Route("/guest-book/register", name="user_register")
     */
    public function register(UserPasswordEncoderInterface $passwordEncoder, Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $passwordEncoder->encodePassword(
                $user, 
                $user->getPlainPassword()
            );
            $user->setPassword($password);
            $user->setLastVisit(new \DateTime('@'.strtotime('now')));
            $user->setRoles([User::ROLE_USER]);
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            return new RedirectResponse($this->router->generate('messages_index'));
        }

        return $this->render('guest-book/register.html.twig', [
            'form' => $form->createView()
        ]);
    }
}