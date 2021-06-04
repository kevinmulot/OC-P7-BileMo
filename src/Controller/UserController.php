<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Cache\InvalidArgumentException;
use RuntimeException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Swagger\Annotations as SWG;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security as SecurityFilter;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use App\Service\CacheManager;

/**
 * Class UserController
 * @package App\Controller
 * @Route("/api")
 */
class UserController extends AbstractController
{
    /**
     * @var UserRepository
     */
    private $userRepository;

    /**
     * @var PaginatorInterface
     */
    private $paginate;

    /**
     * @var ClientRepository
     */
    private $clientRepository;

    public function __construct(UserRepository $userRepository, PaginatorInterface $paginator, ClientRepository $clientRepository)
    {
        $this->userRepository = $userRepository;
        $this->clientRepository = $clientRepository;
        $this->paginate = $paginator;
    }

    /**
     *
     * @param CacheInterface $cache
     * @param Request $request
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
     *     summary="Get the list of users",
     *     @SWG\Response(response="200",
     *     description="Return a list of users")
     * )
     * @SWG\Tag(name="Users")
     */
    public function listUsers(CacheInterface $cache, Request $request, SecurityFilter $security)
    {
        $page = $request->query->getInt('page', 1);
        $loggedClient = $this->clientRepository->findOneBy(["username" => $security->getUser()->getUsername()]);

        if (in_array("ROLE_ADMIN", $loggedClient->getRoles(), true)) {
            $value = $cache->get('users_list' . $page, function (ItemInterface $item)
            use ($page) {
                $item->expiresAfter(3600);

                $query = $this->userRepository->findAll();

                return $this->paginate->paginate(
                    $query,
                    $page,
                    10
                );
            });
            return $value->getItems();
        }

        $clientId = $loggedClient->getId();

        $value = $cache->get($clientId . 'users_list' . $page, function (ItemInterface $item)
        use ($page, $clientId) {
            $item->expiresAfter(3600);

            $query = $this->userRepository->findBy(['client' => $clientId]);

            return $this->paginate->paginate(
                $query,
                $page,
                10
            );
        });

        return $value->getItems();
    }

    /**
     *
     * @param User $user
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
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="number",
     *     description="User id"
     * )
     * @Rest\View(
     *     statusCode= 200,
     *     serializerGroups={"show"}
     * )
     * @SWG\Get(
     *     summary="Display a specific user",
     *     @SWG\Response(response="200", description="Return a user details")
     * )
     * @SWG\Tag(name="Users")
     */
    public function showUser(User $user, $id, CacheInterface $cache, SecurityFilter $security)
    {
        $loggedClient = $this->clientRepository->findOneBy(["username" => $security->getUser()->getUsername()]);

        if ($loggedClient === $user->getClient() || in_array("ROLE_ADMIN", $loggedClient->getRoles(), true)) {
            return $cache->get('user' . $id, function (ItemInterface $item)
            use ($id) {
                $item->expiresAfter(3600);

                return $this->userRepository->findOneBy(['id' => $id]);
            });
        }
    }

    /**
     * @param User $user
     * @param EntityManagerInterface $manager
     * @param ValidatorInterface $validator
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
     *          @SWG\Property(property="username", type="string")
     *     )
     * )
     * @SWG\Parameter(
     *     name="first_name",
     *     in="body",
     *     type="string",
     *     description="First name",
     *     required=true,
     *     @SWG\Schema(
     *          @SWG\Property(property="first_name", type="string")
     *     )
     * )
     * @SWG\Parameter(
     *     name="last_name",
     *     in="body",
     *     type="string",
     *     description="Last name",
     *     required=true,
     *     @SWG\Schema(
     *          @SWG\Property(property="last_name", type="string")
     *     )
     * )
     * @SWG\Parameter(
     *     name="email",
     *     in="body",
     *     type="string",
     *     description="Email",
     *     required=true,
     *     @SWG\Schema(
     *          @SWG\Property(property="email", type="string")
     *     )
     * )
     * @SWG\Post(
     *     summary="Create a new user (required fields : username, firstname, lastname, email / required role : user)",
     *     @SWG\Response(response="201", description="Return a new user")
     * )
     * @Security("is_granted('ROLE_USER')")
     *
     * @SWG\Tag(name="Users")
     */
    public function addUser(User $user, EntityManagerInterface $manager, ValidatorInterface $validator): User
    {
        $errors = $validator->validate($user);

        if (count($errors)) {
            throw new RuntimeException($errors);
        }

        $user->setClient($user->getClient());
        $manager->persist($user);
        $manager->flush();

        return $user;
    }

    /**
     * @param User $user
     * @param User $newUser
     * @param ValidatorInterface $validator
     * @param SecurityFilter $security
     * @param CacheInterface $cache
     * @param CacheManager $cacheManager
     * @return mixed
     * @throws InvalidArgumentException
     * @Rest\Put(
     *     path="/users/{id}",
     *     name="update_user",
     *     requirements={"id"="\d+"}
     * )
     * @ParamConverter("newUser", converter="fos_rest.request_body")
     * @Rest\View(statusCode= 200,
     * serializerGroups={"show"})
     *
     * @SWG\Put(
     *     summary="Update a specific user",
     *     @SWG\Response(response="200", description="Return the updated user")
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="number",
     *     description="User id"
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
     *     name="first_name",
     *     in="body",
     *     type="string",
     *     description="First name",
     *     required=false,
     *     @SWG\Schema(
     *          @SWG\Property(property="first_name", type="string")
     *     )
     * )
     * @SWG\Parameter(
     *     name="last_name",
     *     in="body",
     *     type="string",
     *     description="Last name",
     *     required=false,
     *     @SWG\Schema(
     *          @SWG\Property(property="last_name", type="string")
     *     )
     * )
     * @SWG\Parameter(
     *     name="email",
     *     in="body",
     *     type="string",
     *     description="Email",
     *     required=false,
     *     @SWG\Schema(
     *          @SWG\Property(property="email", type="string")
     *     )
     * )
     * @SWG\Tag(name="Users")
     */
    public function updateUser(User $user, User $newUser, ValidatorInterface $validator, SecurityFilter $security, CacheInterface $cache, CacheManager $cacheManager)
    {
        $loggedClient = $this->clientRepository->findOneBy(["username" => $security->getUser()->getUsername()]);

        if ($loggedClient !== $user->getClient()) {
            throw new AccessDeniedException();
        }
        if ($newUser->getUsername()) {
            $user->setUsername($newUser->getUsername());
        }

        if ($newUser->getFirstName()) {
            $user->setFirstName($newUser->getFirstName());
        }

        if ($newUser->getLastName()) {
            $user->setLastName($newUser->getLastName());
        }

        if ($newUser->getEmail()) {
            $user->setEmail($newUser->getEmail());
        }

        $errors = $validator->validate($user);
        if (count($errors)) {
            throw new RuntimeException('Invalid argument(s) detected');
        }

        $this->getDoctrine()->getManager()->flush();
        $cacheManager->deleteCache($cache, $user->getId(), "user");
        $cacheManager->deleteCustomerCache($cache, $user->getClient()->getId());

        return $user;
    }

    /**
     * @param User $user
     * @param EntityManagerInterface $manager
     * @param SecurityFilter $security
     * @param CacheInterface $cache
     * @param CacheManager $cacheManager
     * @throws InvalidArgumentException
     * @Rest\Delete(
     *     path="/users/{id}",
     *     name="delete_user",
     *     requirements={"id"="\d+"}
     * )
     * @Rest\View(statusCode= 204)
     * @SWG\Delete(
     *     summary="Delete a specific user",
     *     @SWG\Response(response="204", description="Return an empty body")
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="number",
     *     description="User id"
     * )
     * @SWG\Tag(name="Users")
     */
    public function deleteUser(User $user, entityManagerInterface $manager, SecurityFilter $security, CacheInterface $cache, CacheManager $cacheManager): void
    {
        $loggedClient = $this->clientRepository->findOneBy(["username" => $security->getUser()->getUsername()]);

        if ($loggedClient === $user->getClient() || in_array("ROLE_ADMIN", $loggedClient->getRoles(), true)) {
            $cacheManager->deleteCache($cache, $user->getId(), 'user');
            $cacheManager->deleteCustomerCache($cache, $user->getClient()->getId());
            $manager->remove($user);
            $manager->flush();
        }
    }
}
