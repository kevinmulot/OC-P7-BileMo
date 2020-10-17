<?php

namespace App\DataFixtures;

use App\Entity\Phone;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class PhoneFixtures extends Fixture
{
    private $names = ['iPhone', 'Samsung'];

    public function load(ObjectManager $manager)
    {
        for($i = 1; $i <= 20; $i++) {
            $phone = new Phone();
            $phone->setName($this->names[random_int(0,1)]. ' ' . random_int(5, 8));
            $phone->setPrice(random_int(500, 1000));
            $phone->setDescription('A wonderful phone with ' . random_int(10, 50) . ' tricks');

            $manager->persist($phone);
        }
        $manager->flush();
    }
}
