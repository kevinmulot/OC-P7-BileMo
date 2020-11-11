<?php

namespace App\Controller;

use App\Entity\Client;
use FOS\RestBundle\Controller\Annotations as Rest;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class SecurityController
 * @package App\Controller
 * @Route("/api")
 */
class SecurityController extends AbstractController
{

    /**
     * @Rest\Post(
     *     path="/login_check",
     *     name="login"
     * )
     * @Rest\View(statusCode= 200)
     *
     * @SWG\Post(
     *     summary="fill your username and password (required field : username, password) ",
     *     @SWG\Response(response="200", description="Return a token for authentification")
     * )
     */
    public function login()
    {
        return $this->getUser();
    }

    /**
     * @Rest\Post(
     *     path="/register",
     *     name="register"
     * )
     * @Rest\View(statusCode= 201)
     * @ParamConverter("client", converter="fos_rest.request_body")
     *
     * @SWG\Post(
     *     summary="Enter a username and password (required fields : username, password)",
     *     @SWG\Response(response="200", description="Return a new client")
     * )
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     * @param Client $client
     * @param UserPasswordEncoderInterface $encoder
     * @param EntityManagerInterface $manager
     * @return mixed
     */
    public function register(Client $client, UserPasswordEncoderInterface $encoder, EntityManagerInterface $manager)
    {
        $client->setPassword($encoder->encodePassword($client, $client->getPassword()));
        $client->setRoles(["ROLE_ADMIN"]);

        $manager->persist($client);
        $manager->flush();

        return $client;
    }

}
