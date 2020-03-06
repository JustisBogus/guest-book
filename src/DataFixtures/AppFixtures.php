<?php

namespace App\DataFixtures;

use App\Entity\Message;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private const USERS = [
        [
            'username' => 'justis',
            'email' => 'justis@mail.com',
            'password' => 'login1',
            'roles' => [User::ROLE_ADMIN]
        ],
        [
            'username' => 'kazimieras',
            'email' => 'kazimieras@mail.com',
            'password' => 'login1',
            'roles' => [User::ROLE_USER]
        ],
        [
            'username' => 'aistee',
            'email' => 'aistee@mail.com',
            'password' => 'login1',
            'roles' => [User::ROLE_USER]
        ],
    ];

    private const MESSAGE_TEXT = [
        'Hello',
        'How does it work?',
        'Some people are awesome',
        'I like cats',
        'I like dogs',
        'My browser has a lot of tabs open',
        'Coding is fun'
    ];

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;

    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function load(ObjectManager $manager)
    {
        $this->loadUsers($manager);
        $this->loadMessages($manager);
    }

    private function loadMessages(ObjectManager $manager)
    {
        for ($i = 0; $i < 30; $i++) {
            $message = new Message();
            $message->setText(
                self::MESSAGE_TEXT[rand(0, count(self::MESSAGE_TEXT) - 1)]
            );
            $date = new \DateTime();
            $date->modify('-' . rand(0, 10) . 'day');
            $message->setTime($date);
            $message->setUser($this->getReference(
                self::USERS[rand(0, count(self::USERS) - 1)]['username']
            ));
            $manager->persist($message);
        }

        $manager->flush();
    }

    private function loadUsers(ObjectManager $manager)
    {
        foreach (self::USERS as $userData) {
        $user = new User();
        $user->setUsername($userData['username']);
        $user->setEmail($userData['email']);
        $user->setPassword($this->passwordEncoder->encodePassword($user, $userData['password']));
        $user->setLastVisit(new \DateTime('2020-03-03'));
        $this->addReference($userData['username'], $user);
        $user->setRoles($userData['roles']);
        $manager->persist($user);
        }
        $manager->flush();
    }
}
