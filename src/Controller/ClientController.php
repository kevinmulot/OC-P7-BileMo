<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
use App\Service\CacheManager;
use RuntimeException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Cache\InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Swagger\Annotations as SWG;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * Class ClientController
 * @package App\Controller
 * @Route("/api")
 */
class ClientController extends AbstractController
{
    private $paginate;
    /**
     * @var CacheInterface
     */
    private $cache;
    /**
     * @var CacheManager
     */
    private $cacheManager;
    /**
     * @var ClientRepository
     */
    private $clientRepository;

    public function __construct(ClientRepository $clientRepository, PaginatorInterface $paginator, CacheInterface $cache, CacheManager $cacheManager)
    {
        $this->clientRepository = $clientRepository;
        $this->paginate = $paginator;
        $this->cache = $cache;
        $this->cacheManager = $cacheManager;
    }

    /**
     *
     * @param Request $request
     * @return mixed
     * @throws InvalidArgumentException
     * @Rest\Get(
     *     path="/clients",
     *     name="clients_list"
     * )
     * @Rest\View(
     *     statusCode= 200,
     *     serializerGroups={"list"}
     * )
     *
     * @SWG\Get(
     *     summary="Get the list of clients (required role : admin)",
     *     @SWG\Response(response="200", description="Return a list of clients")
     * )
     * @SWG\Tag(name="Clients")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function listClients(Request $request)
    {
        $page = $request->query->getInt('page', 1);

        $value = $this->cache->get('clients_list' . $page, function (ItemInterface $item)
        use ($page) {
            $item->expiresAfter(3600);

            $query = $this->clientRepository->findAll();

            return $this->paginate->paginate(
                $query,
                $page,
                10
            );
        });

        return $value->getItems();
    }

    /**
     * @param $id
     * @return mixed
     * @throws InvalidArgumentException
     * @Rest\Get(
     *     path="/clients/{id}",
     *     name="show_client",
     *     requirements={"id"="\d+"}
     * )
     * @Rest\View(statusCode= 200, serializerGroups={"show"})
     *
     * @SWG\Get(
     *     summary="Display a specific client (required role : admin)",
     *     @SWG\Response(response="200", description="Return a client details")
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="number",
     *     description="Client id"
     * )
     * @SWG\Tag(name="Clients")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function showClient($id)
    {
        return $this->cache->get('client' . $id, function (ItemInterface $item) use ($id) {
            $item->expiresAfter(3600);

            return $this->clientRepository->find($id);
        });
    }

    /**
     * @param Client $client
     * @param Client $newClient
     * @param ValidatorInterface $validator
     * @param UserPasswordEncoderInterface $encoder
     * @return mixed
     * @throws InvalidArgumentException
     * @Rest\Put(
     *     path="/clients/{id}",
     *     name="update_client",
     *     requirements={"id"="\d+"}
     *
     * )
     * @ParamConverter("newClient", converter="fos_rest.request_body")
     * @Rest\View(statusCode= 200,
     *     serializerGroups={"secret"})
     *
     * @SWG\Put(
     *     summary="Update logged client (required role : admin)",
     *     @SWG\Response(response="200", description="Update a specific client")
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="number",
     *     description="Client id"
     * )
     * @SWG\Parameter(
     *     name="username",
     *     in="body",
     *     type="string",
     *     description="Username",
     *     required=false,
     *     @SWG\Schema(
     *          @SWG\Property(property="username", type="string")
     *     )
     * )
     * @SWG\Parameter(
     *     name="password",
     *     in="body",
     *     type="string",
     *     description="Password",
     *     required=false,
     *     @SWG\Schema(
     *          @SWG\Property(property="password", type="string")
     *     )
     * )
     * @SWG\Tag(name="Clients")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function updateClient(Client $client, Client $newClient, ValidatorInterface $validator, UserPasswordEncoderInterface $encoder)
    {
        if ($newClient->getUsername()) {
            $client->setUsername($newClient->getUsername());
        }
        if ($newClient->getPassword()) {
            $client->setPassword($encoder->encodePassword($newClient, $newClient->getPassword()));
        }

        $errors = $validator->validate($client);
        if (count($errors)) {
            throw new RuntimeException('Invalid argument(s) detected');
        }

        $this->getDoctrine()->getManager()->flush();

        $this->cacheManager->deleteUserCache($this->cache, $client->getId());

        return $client;
    }

    /**
     * @param Client $client
     * @param EntityManagerInterface $manager
     * @throws InvalidArgumentException
     * @Rest\Delete(
     *     path="/clients/{id}",
     *     name="delete_client",
     *     requirements={"id"="\d+"}
     * )
     * @Rest\View(statusCode= 204)
     * @SWG\Delete(
     *     summary="Delete a specific client (required role : admin)",
     *     @SWG\Response(response="204", description="Return an empty body")
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="number",
     *     description="Client id"
     * )
     * @SWG\Tag(name="Clients")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function deleteClient(Client $client, EntityManagerInterface $manager): void
    {
        $this->cacheManager->deleteClientCache($this->cache, $client->getId());
        $manager->remove($client);
        $manager->flush();
    }
}
