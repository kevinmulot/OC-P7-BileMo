<?php

namespace App\Controller;

use App\Entity\Client;
use App\Repository\ClientRepository;
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
 * @Route("/api", name="client")
 */
class ClientController extends AbstractController
{
    private $repo;
    private $paginate;

    public function __construct(ClientRepository $clientRepository, PaginatorInterface $paginator)
    {
        $this->repo = $clientRepository;
        $this->paginate = $paginator;
    }

    /**
     *
     * @param CacheInterface $cache
     * @param Request $request
     *
     * @return mixed
     * @throws InvalidArgumentException
     * @Rest\Get(
     *     path="/clients",
     *     name="client_list"
     * )
     * @Rest\View(
     *     statusCode= 200,
     *     serializerGroups={"list"}
     * )
     *
     * @SWG\Get(
     *     @SWG\Response(response="200", description="Return a list of clients")
     * )
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     */
    public function listClients(CacheInterface $cache, Request $request)
    {
        $page = $request->query->getInt('page', 1);

        $value = $cache->get('client_list' . $page, function (ItemInterface $item)
        use ($page) {
            $item->expiresAfter(3600);

            $query = $this->repo->findAll();

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
     * @param CacheInterface $cache
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
     *     @SWG\Response(response="200", description="Return a client details")
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="number",
     *     description="The id of the client"
     * )
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     */
    public function showClient($id, CacheInterface $cache)
    {
        return $cache->get('show_client' . $id, function (ItemInterface $item) use ($id) {
            $item->expiresAfter(3600);

            return $this->repo->find($id);
        });
    }

    /**
     * @param Client $client
     * @param EntityManagerInterface $manager
     * @Rest\Delete(
     *     path="/clients/{id}",
     *     name="delete_client",
     *     requirements={"id"="\d+"}
     * )
     * @Rest\View(statusCode= 204)
     * @SWG\Delete(
     *     @SWG\Response(response="204", description="Delete a specific client")
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="number",
     *     description="The id of the client"
     * )
     * @Security("is_granted('ROLE_SUPERADMIN')")
     */
    public function deleteClient(Client $client, EntityManagerInterface $manager): void
    {
        $manager->remove($client);
        $manager->flush();
    }

    /**
     * @param EntityManagerInterface $manager
     * @param $id
     * @param Client $client
     * @param ValidatorInterface $validator
     * @param UserPasswordEncoderInterface $encoder
     * @return Client|object|null
     * @Rest\Patch(
     *     path="/clients/{id}",
     *     name="client_update",
     *     requirements={"id"="\d+"}
     * )
     * @Rest\View(statusCode= 200)
     *
     * @SWG\Patch(
     *     @SWG\Response(response="200", description="Update client")
     * )
     * @ParamConverter("client", converter="fos_rest.request_body")
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="number",
     *     description="The id of the client"
     * )
     * @SWG\Parameter(
     *     name="username",
     *     in="body",
     *     type="string",
     *     description="Name",
     *     required=true,
     *     @SWG\Schema(
     *          @SWG\Property(property="name", type="string")
     *     )
     * )
     * @SWG\Parameter(
     *     name="password",
     *     in="body",
     *     type="string",
     *     description="Password",
     *     required=true,
     *     @SWG\Schema(
     *          @SWG\Property(property="price", type="integer")
     *     )
     * )
     * @SWG\Parameter(
     *     name="role",
     *     in="body",
     *     type="string",
     *     description="Role",
     *     required=true,
     *     @SWG\Schema(
     *          @SWG\Property(property="Description", type="string")
     *     )
     * )
     * @Security("is_granted('ROLE_SUPERADMIN')")
     */
    public function patchClient(EntityManagerInterface $manager, $id, Client $client, ValidatorInterface $validator, UserPasswordEncoderInterface $encoder)
    {
        $result = $manager->getRepository(Client::class)->findOneBy(['id' => $id]);

        $errors = $validator->validate($result);
        if (count($errors)) {
            throw new \RuntimeException('Invalid argument(s) detected');
        }

        $result->setUsername($client->getUsername());
        $result->setPassword($encoder->encodePassword($client, $client->getPassword()));
        $result->setRoles([$client->getRoles()]);

        $manager->persist($result);
        $manager->flush();

        return $result;
    }

}
