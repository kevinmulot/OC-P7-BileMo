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

    private $firstNames = ['Kevin','John', 'Bob', 'Samuel', 'Cedric', 'Etienne', 'Jean', 'Marc', 'Romain', 'Tom'];

    private $lastNames = ['Wick','Weak', 'London', 'Paris', 'Tokyo', 'Mulot', 'Briant', 'Curry','Mandela', 'Dream'];

    /**
     * @var UserPasswordEncoderInterface
     */
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->encoder = $encoder;
    }

    public function load(ObjectManager $manager)
    {
        $client = new Client();
        $client->setUsername('keimuo');
        $client->setPassword($this->encoder->encodePassword($client, 'blackberry'));
        $client->setRoles(['ROLE_SUPERADMIN']);

        $manager->persist($client);

            for ($i = 1; $i <= 20; $i++) {
                $phone = new Phone();
                $phone->setName($this->phoneNames[random_int(0,3)]. ' ' . random_int(5, 8));
                $phone->setPrice(random_int(500, 1000));
                $phone->setDescription('A wonderful phone with ' . random_int(10, 50) . ' tricks');

                $manager->persist($phone);

                $client = new Client();
                $client->setUsername('client' . $i);
                $client->setPassword($this->encoder->encodePassword($client, 'clientpass'));
                $client->setRoles(['ROLE_ADMIN']);

                $manager->persist($client);

                $user = new User();
                $user->setUsername('user' . $i);
                $user->setFirstName($this->firstNames[random_int(0, 9)]);
                $user->setLastName($this->lastNames[random_int(0, 9)]);
                $user->setEmail('usermail' . $i . '@hotmail.fr');
                $user->setPassword($this->encoder->encodePassword($user, 'userpass'));
                $user->setRoles(['ROLE_USER']);
                $user->setClient($client);

                $manager->persist($user);
            }

            $manager->flush();
        }
}
