<?php

namespace App\DataFixtures;

use App\Entity\Message;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class AppFixtures extends Fixture
{
    public function load(ObjectManager $manager)
    {
        for ($i = 0; $i < 10; $i++) {
            $message = new Message();
            $message->setText('Some random text'. rand(0, 100));
            $message->setTime(new \DateTime('2020-03-02'));
            $manager->persist($message);
        }

        $manager->flush();
    }
}
