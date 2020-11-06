<?php

namespace App\DataFixtures;

use App\Entity\Client;
use App\Entity\Phone;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AppFixtures extends Fixture
{
    private $phoneNames = ['iPhone', 'Samsung', 'Nokia', 'Huawei'];

    private $clientNames = ['','', '', ''];

    private $userNames = ['','', '', ''];

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        for($i = 1; $i <= 20; $i++) {
            $phone = new Phone();
            $phone->setName($this->phoneNames[random_int(0,1)]. ' ' . random_int(5, 8));
            $phone->setPrice(random_int(500, 1000));
            $phone->setDescription('A wonderful phone with ' . random_int(10, 50) . ' tricks');

            $manager->persist($phone);
        }
            for ($i = 1; $i <= 20; $i++) {
                $client = new Client();
                $client->setUsername($this->clientNames[random_int(0, 1)] . ' ' . random_int(5, 8));
                $client->setPassword($encoder->encodePassword($client, $client->getPassword()));
                $client->setRoles(['ROLE_ADMIN']);

                $manager->persist($client);
            }

            for ($i = 1; $i <= 20; $i++) {
                $user = new User();
                $user->setFirstName($this->userNames[random_int(0, 1)] . ' ' . random_int(5, 8));
                $user->setLastName(random_int(500, 1000));
                $user->setEmail('A wonderful phone with ' . random_int(10, 50) . ' tricks');
                $user->setPassword($encoder->encodePassword($user, $user->getPassword()));

                $manager->persist($user);
            }
            $manager->flush();
        }
}
