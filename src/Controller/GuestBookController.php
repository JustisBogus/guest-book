<?php

namespace App\Controller;

use App\Entity\Message;
use App\Form\MessageType;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;

/**
 * @Route("/guest-book")
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
    public function index() 
    {
        return $this->render('guest-book/index.html.twig', [
            'messages' => $this->messageRepository->findBy([], ['time' => 'DESC'])
        ]);
    }

    /**
     * @Route("/edit/{id}", name="message_edit")
     */
    public function edit(Message $message, Request $request)
    {
        $form = $this->formFactory->create(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            return new RedirectResponse($this->router->generate('messages_index'));
        }
        
        return $this->render('guest-book/add.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/delete/{id}", name="message_delete")
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
     */
    public function add(Request $request)
    {
        $message = new Message();
        $message->setTime(new \DateTime());

        $form = $this->formFactory->create(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($message);
            $this->entityManager->flush();

            return new RedirectResponse($this->router->generate('messages_index'));
        }
        
        return $this->render('guest-book/add.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/{id}", name="message")
     */
    public function message(Message $message)
    {
        return $this->render('guest-book/message.html.twig', ['message' => $message]);
    }
}