<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\MessageType;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * @Route("/")
 */
class GuestBookController extends AbstractController
{

    /**
     * @var MessageRepository
     */
    private $messageRepository;

    /**
     * @var FormFactoryInterface
     */
    private $formFactory;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var FlashBagInterface
     */
    private $flashBag;

    public function __construct(MessageRepository $messageRepository,
        FormFactoryInterface $formFactory, EntityManagerInterface $entityManager,
        RouterInterface $router, FlashBagInterface $flashBag)
    {
        $this->messageRepository = $messageRepository;
        $this->formFactory = $formFactory;
        $this->entityManager = $entityManager;
        $this->router = $router;
        $this->flashBag = $flashBag;
    }

    /**
     * @Route("/", name="messages_index")
     */
    public function index(Request $request ,PaginatorInterface $paginator, TokenStorageInterface $tokenStorage) 
    {
        $securityContext = $this->container->get('security.authorization_checker');
            if ($securityContext->isGranted('ROLE_USER')) {
                $user = $tokenStorage->getToken()->getUser();

                $user->setLastVisit(new \DateTime());

                $this->entityManager->persist($user);
                $this->entityManager->flush();
            }

        $messages = $this->messageRepository->findBy([], ['time' => 'DESC']);
        $pagination = $paginator->paginate(
            $messages,
            $request->query->getInt('page', 1),
            10 
        );

        return $this->render('guest-book/index.html.twig', [
            'messages' => $pagination
        ]);
    }

    /**
     * @Route("/admin", name="guest_book_admin")
     */
    public function admin(Request $request ,PaginatorInterface $paginator, TokenStorageInterface $tokenStorage) {

        $securityContext = $this->container->get('security.authorization_checker');
            if ($securityContext->isGranted('ROLE_ADMIN')) {
                $user = $tokenStorage->getToken()->getUser();

                $user->setLastVisit(new \DateTime());

                $this->entityManager->persist($user);
                $this->entityManager->flush();
            }

        $messages = $this->messageRepository->findBy([], ['time' => 'DESC']);
        $pagination = $paginator->paginate(
            $messages,
            $request->query->getInt('page', 1),
            10 
        );

        return $this->render('guest-book/admin.html.twig', [
            'messages' => $pagination
        ]);
    }

    /**
     * @Route("/edit/{id}", name="message_edit")
     * @Security("is_granted('edit', message)", message="Access denied")
     */
    public function edit(Message $message, Request $request, TokenStorageInterface $tokenStorage)
    {
        $user = $tokenStorage->getToken()->getUser();
        $username = $user->getUsername();
        $form = $this->formFactory->create(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return new RedirectResponse($this->router->generate('messages_index'));
        }
        
        return $this->render('guest-book/add.html.twig', ['form' => $form->createView(), 'username' => $username]);
    }

    /**
     * @Route("/delete/{id}", name="message_delete")
     * @Security("is_granted('delete', message)", message="Access denied")
     */
    public function delete(Message $message)
    {
        $this->entityManager->remove($message);
        $this->entityManager->flush();

        $this->flashBag->add('notice', 'Message was deleted');

        return new RedirectResponse($this->router->generate('messages_index'));
    }

    /**
     * @Route("/add", name="message_add")
     * @Security("is_granted('ROLE_USER')")
     */
    public function add(Request $request, TokenStorageInterface $tokenStorage)
    {
        $user = $tokenStorage->getToken()->getUser();
        $username = $user->getUsername();
        $message = new Message();
        $message->setUser($user);
        $message->setTime(new \DateTime());
        $form = $this->formFactory->create(MessageType::class, $message);
        $form->handleRequest($request);
        $honeyPot = $message->getHoneyPot();

        if ($form->isSubmitted() && $form->isValid()) {
            if (empty($honeyPot)) {
                $this->entityManager->persist($message);
                $this->entityManager->flush();
            } else {
                $this->flashBag->add('notice', 'Spam');
            }
            return new RedirectResponse($this->router->generate('messages_index'));
        }
        
        return $this->render('guest-book/add.html.twig', ['form' => $form->createView(), 'username' => $username]);
    }

    /**
     * @Route("/{id}", name="message")
     */
    public function message(Message $message)
    {
        return $this->render('guest-book/message.html.twig', ['message' => $message]);
    }

    /**
     * @Route("/logout", name="security_logout", methods={"GET"})
     */
    public function logout()
    {
        //Won't work with http basic login
    }

}