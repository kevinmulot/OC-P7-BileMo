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
     * @SWG\Parameter(
     *     name="username",
     *     in="body",
     *     type="string",
     *     description="Username",
     *     required=true,
     *     @SWG\Schema(
     *          @SWG\Property(property="username", type="string")
     *     )
     * )
     * @SWG\Parameter(
     *     name="Password",
     *     in="body",
     *     type="string",
     *     description="Password",
     *     required=true,
     *     @SWG\Schema(
     *          @SWG\Property(property="password", type="string")
     *     )
     * )
     * @SWG\Post(
     *     summary="Get a bearer token for authorization (required field : username, password) ",
     *     @SWG\Response(response="200", description="Return a token for authentification")
     * )
     * @SWG\Tag(name="Security")
     */
    public function login()
    {
        return $this->getUser();
    }

    /**
     *
     * @param Client $client
     * @param UserPasswordEncoderInterface $encoder
     * @param EntityManagerInterface $manager
     * @return mixed
     * @Rest\Post(
     *     path="/register",
     *     name="register"
     * )
     * @Rest\View(statusCode= 201)
     * @ParamConverter("client", converter="fos_rest.request_body")
     *
     * @SWG\Post(
     *     summary="Register as a new client (required fields : username, password)",
     *     @SWG\Response(response="200", description="Return a new client")
     * )
     * @SWG\Parameter(
     *     name="username",
     *     in="body",
     *     type="string",
     *     description="Username",
     *     required=true,
     *     @SWG\Schema(
     *          @SWG\Property(property="username", type="string")
     *     )
     * )
     * @SWG\Parameter(
     *     name="Password",
     *     in="body",
     *     type="string",
     *     description="Password",
     *     required=true,
     *     @SWG\Schema(
     *          @SWG\Property(property="password", type="string")
     *     )
     * )
     * @SWG\Tag(name="Security")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function register(Client $client, UserPasswordEncoderInterface $encoder, EntityManagerInterface $manager)
    {
        $client->setPassword($encoder->encodePassword($client, $client->getPassword()));
        $client->setRoles(["ROLE_USER"]);

        $manager->persist($client);
        $manager->flush();

        return $client;
    }
}
