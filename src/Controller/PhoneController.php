<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use App\Service\CacheManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use FOS\RestBundle\Controller\Annotations as Rest;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Cache\InvalidArgumentException;
use RuntimeException;
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
 * Class PhoneController
 * @package App\Controller
 * @Route("/api")
 */
class PhoneController extends AbstractController
{
    /**
     * @var PhoneRepository
     */
    private $repo;
    /**
     * @var PaginatorInterface
     */
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
     * PhoneController constructor.
     * @param PhoneRepository $phoneRepository
     * @param PaginatorInterface $paginator
     * @param CacheInterface $cache
     * @param CacheManager $cacheManager
     */
    public function __construct(PhoneRepository $phoneRepository, PaginatorInterface $paginator, CacheInterface $cache, CacheManager $cacheManager)
    {
        $this->repo = $phoneRepository;
        $this->paginate = $paginator;
        $this->cache = $cache;
        $this->cacheManager = $cacheManager;
    }

    /**
     * @param Request $request
     * @return mixed
     * @throws InvalidArgumentException
     * @Rest\Get(
     *     path="/phones",
     *     name="phones_list"
     * )
     * @Rest\View(
     *     statusCode= 200,
     *     serializerGroups={"list"}
     * )
     *
     * @SWG\Get(
     *     summary="Get the list of phones",
     *     @SWG\Response(response="200", description="Return a list of phones")
     * )
     * @SWG\Tag(name="Phones")
     */
    public function listPhones(Request $request)
    {
        $page = $request->query->getInt('page', 1);

        $value = $this->cache->get('phones_list' . $page, function (ItemInterface $item)
        use ($page) {
            $item->expiresAfter(3600);

            $query = $this->repo->findAll();

            return $this->paginate->paginate($query, $page, 10);
        });

        return $value->getItems();
    }

    /**
     * @param $id
     * @return mixed
     * @throws InvalidArgumentException
     * @Rest\Get(
     *     path="/phones/{id}",
     *     name="show_phone",
     *     requirements={"id"="\d+"}
     * )
     * @Rest\View(statusCode= 200, serializerGroups={"show"})
     *
     * @SWG\Get(
     *     summary="Display a specific phone",
     *     @SWG\Response(response="200", description="Return phone details")
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="number",
     *     description="Phone id"
     * )
     * @SWG\Tag(name="Phones")
     */
    public function showPhone($id)
    {
        return $this->cache->get('phone' . $id, function (ItemInterface $item) use ($id) {
            $item->expiresAfter(3600);

            return $this->repo->find($id);
        });
    }

    /**
     * @param Phone $phone
     * @param EntityManagerInterface $manager
     * @param ValidatorInterface $validator
     * @return Phone
     * @throws Exception
     * @Rest\Post(
     *     path="/phones",
     *     name="add_phone"
     * )
     * @Rest\View(statusCode= 201)
     * @ParamConverter("phone", converter="fos_rest.request_body")
     * @SWG\Parameter(
     *     name="name",
     *     in="body",
     *     type="string",
     *     description="Name",
     *     required=true,
     *     @SWG\Schema(
     *          @SWG\Property(property="name", type="string")
     *     )
     * )
     * @SWG\Parameter(
     *     name="price",
     *     in="body",
     *     type="integer",
     *     description="Price",
     *     required=true,
     *     @SWG\Schema(
     *          @SWG\Property(property="price", type="integer")
     *     )
     * )
     * @SWG\Parameter(
     *     name="description",
     *     in="body",
     *     type="string",
     *     description="Description",
     *     required=true,
     *     @SWG\Schema(
     *          @SWG\Property(property="description", type="string")
     *     )
     * )
     * @SWG\Post(
     *     summary="Create a new phone (required role : admin)",
     *     @SWG\Response(response="201", description="Return a new phone")
     * )
     * @SWG\Tag(name="Phones")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function addPhone(Phone $phone, EntityManagerInterface $manager, ValidatorInterface $validator): Phone
    {
        $errors = $validator->validate($phone);

        if (count($errors)) {
            throw new RuntimeException($errors);
        }

        $manager->persist($phone);
        $manager->flush();

        return $phone;
    }

    /**
     * @param Phone $phone
     * @param Phone $newPhone
     * @param ValidatorInterface $validator
     * @return mixed
     * @throws InvalidArgumentException
     * @Rest\Put(
     *     path="/phones/{id}",
     *     name="update_phone",
     *     requirements={"id"="\d+"}
     * )
     * @ParamConverter("newPhone", converter="fos_rest.request_body")
     * @Rest\View(statusCode= 200)
     *
     * @SWG\Put(
     *     summary="Update a specific phone (required role : admin)",
     *     @SWG\Response(response="200", description="Update a specific phone")
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="number",
     *     description="Phone id"
     * )
     * @SWG\Parameter(
     *     name="name",
     *     in="body",
     *     type="string",
     *     description="Name",
     *     required=true,
     *     @SWG\Schema(
     *          @SWG\Property(property="name", type="string")
     *     )
     * )
     * @SWG\Parameter(
     *     name="price",
     *     in="body",
     *     type="integer",
     *     description="Price",
     *     required=false,
     *     @SWG\Schema(
     *          @SWG\Property(property="price", type="integer")
     *     )
     * )
     * @SWG\Parameter(
     *     name="description",
     *     in="body",
     *     type="string",
     *     description="Description",
     *     required=false,
     *     @SWG\Schema(
     *          @SWG\Property(property="description", type="string")
     *     )
     * )
     * @SWG\Tag(name="Phones")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function updatePhone(Phone $phone, Phone $newPhone, ValidatorInterface $validator)
    {
        if ($newPhone->getName()) {
            $phone->setName($newPhone->getName());
        }

        if ($newPhone->getDescription()) {
            $phone->setDescription($newPhone->getDescription());
        }

        if ($newPhone->getPrice()) {
            $phone->setPrice($newPhone->getPrice());
        }

        $errors = $validator->validate($phone);
        if (count($errors)) {
            throw new RuntimeException('Invalid argument(s) detected');
        }

        $this->getDoctrine()->getManager()->flush();
        $this->cacheManager->deleteCache($this->cache, $phone->getId(), 'phone');

        return $phone;
    }

    /**
     * @param Phone $phone
     * @param EntityManagerInterface $manager
     * @throws InvalidArgumentException
     * @Rest\Delete(
     *     path="/phones/{id}",
     *     name="delete_phone",
     *     requirements={"id"="\d+"}
     * )
     * @Rest\View(statusCode= 204)
     * @SWG\Delete(
     *     summary="Delete a specific phone (required role : admin)",
     *     @SWG\Response(response="204", description="Delete a specific phone")
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="number",
     *     description="Phone id"
     * )
     * @SWG\Tag(name="Phones")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function deletePhone(Phone $phone, EntityManagerInterface $manager): void
    {
        $this->cacheManager->deleteCache($this->cache, $phone->getId(), 'phone');
        $manager->remove($phone);
        $manager->flush();
    }
}
