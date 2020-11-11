<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Cache\InvalidArgumentException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security as SecurityFilter;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

/**
 * @Route("/api", name="user")
 */
class UserController extends AbstractController
{
    private $repo;
    private $paginate;

    public function __construct(UserRepository $userRepository, PaginatorInterface $paginator)
    {
        $this->repo = $userRepository;
        $this->paginate = $paginator;
    }

    /**
     *
     * @param User $user
     * @param ClientRepository $clientRepository
     * @param $id
     * @param CacheInterface $cache
     * @param SecurityFilter $security
     * @return mixed
     * @throws InvalidArgumentException
     * @Rest\Get(
     *     path="/users/{id}",
     *     name="show_user",
     *     requirements={"id"="\d+"}
     * )
     * @Rest\View(
     *     statusCode= 200,
     *     serializerGroups={"show"}
     * )
     *
     * @SWG\Get(
     *     @SWG\Response(response="200", description="Return a user")
     * )
     */
    public function showUser(User $user, ClientRepository $clientRepository, $id, CacheInterface $cache, SecurityFilter $security)
    {
        $loggedClient = $clientRepository->findOneBy(["username" => $security->getUser()->getUsername()]);
        if ($loggedClient === $user->getClient() || in_array("ROLE_SUPER_ADMIN", $loggedClient->getRoles(), true)) {
            return $cache->get('show_user' . $id, function (ItemInterface $item)
            use ($id) {
                $item->expiresAfter(3600);

                return $this->repo->findOneBy(['id' => $id]);
            });
        }
    }

    /**
     *
     * @param CacheInterface $cache
     * @param Request $request
     * @param ClientRepository $clientRepository
     * @param SecurityFilter $security
     * @return mixed
     * @throws InvalidArgumentException
     * @Rest\Get(
     *     path="/users",
     *     name="users_list",
     * )
     * @Rest\View(
     *     statusCode= 200,
     *     serializerGroups={"list"}
     * )
     * @SWG\Get(
     *     @SWG\Response(response="200", description="Return a list of users")
     * )
     */
    public function listUsers(CacheInterface $cache, Request $request, ClientRepository $clientRepository, SecurityFilter $security)
    {
        $page = $request->query->getInt('page', 1);
        if (in_array("ROLE_SUPER_ADMIN", $security->getUser()->getRoles(), true)) {
            $value = $cache->get('users_list' . $page, function (ItemInterface $item)
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

        $loggedClient = $clientRepository->findOneBy(["username" => $security->getUser()->getUsername()]);
        $clientId = $loggedClient->getId();

        $query = $this->repo->findBy(['client' => $clientId]);

        return $this->paginate->paginate(
            $query,
            $page,
            10
        );
    }

    /**
     * @param User $user
     * @param EntityManagerInterface $manager
     * @param ValidatorInterface $validator
     *
     * @return User
     * @Rest\Post(
     *     path="/users",
     *     name="add_user"
     * )
     * @Rest\View(statusCode= 201)
     * @ParamConverter("user", converter="fos_rest.request_body")
     * @SWG\Parameter(
     *     name="username",
     *     in="body",
     *     type="string",
     *     description="Username",
     *     required=true,
     *     @SWG\Schema(
     *          @SWG\Property(property="name", type="string")
     *     )
     * )
     * @SWG\Parameter(
     *     name="firstname",
     *     in="body",
     *     type="string",
     *     description="firstname",
     *     required=true,
     *     @SWG\Schema(
     *          @SWG\Property(property="name", type="string")
     *     )
     * )
     * @SWG\Parameter(
     *     name="lastname",
     *     in="body",
     *     type="string",
     *     description="lastname",
     *     required=true,
     *     @SWG\Schema(
     *          @SWG\Property(property="name", type="string")
     *     )
     * )
     * @SWG\Parameter(
     *     name="email",
     *     in="body",
     *     type="string",
     *     description="Email",
     *     required=true,
     *     @SWG\Schema(
     *          @SWG\Property(property="Description", type="string")
     *     )
     * )
     * @SWG\Post(
     *     @SWG\Response(response="201", description="Return a new user")
     * )
     * @Security("is_granted('ROLE_USER')")
     */
    public function addUser(User $user, EntityManagerInterface $manager, ValidatorInterface $validator): User
    {
        $errors = $validator->validate($user);
        if (count($errors)) {
            throw new \RuntimeException($errors);
        }

        $user->setClient($user->getClient());
        $manager->persist($user);
        $manager->flush();

        return $user;
    }

    /**
     * @param User $user
     * @param ClientRepository $clientRepository
     * @param EntityManagerInterface $manager
     * @param SecurityFilter $security
     * @Rest\Delete(
     *     path="/users/{id}",
     *     name="delete_user",
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
     *     description="The id of the user"
     * )
     */
    public function deleteUser(User $user, ClientRepository $clientRepository, entityManagerInterface $manager, SecurityFilter $security): void
    {
        $loggedClient = $clientRepository->findOneBy(["username" => $security->getUser()->getUsername()]);
        if ($loggedClient === $user->getClient() || in_array("ROLE_SUPER_ADMIN", $loggedClient->getRoles(), true)) {

            $manager->remove($user);
            $manager->flush();
        }
    }

}
