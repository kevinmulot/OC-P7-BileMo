<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
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
 * @Route("/api/phones", name="phone")
 */
class PhoneController extends AbstractController
{
    private $repo;
    private $paginate;

    public function __construct(PhoneRepository $phoneRepository, PaginatorInterface $paginator)
    {
        $this->repo = $phoneRepository;
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
     *     path="/",
     *     name="phone_list"
     * )
     * @Rest\View(
     *     statusCode= 200,
     *     serializerGroups={"list"}
     * )
     *
     * @SWG\Get(
     *     @SWG\Response(response="200", description="Return a list of phones")
     * )
     */
    public function listPhones(CacheInterface $cache, Request $request)
    {
        $page = $request->query->getInt('page', 1);

        $value = $cache->get('phone_list' . $page, function (ItemInterface $item)
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
     *     path="/{id}",
     *     name="show_phone",
     *     requirements={"id"="\d+"}
     * )
     * @Rest\View(statusCode= 200, serializerGroups={"show"})
     *
     * @SWG\Get(
     *     @SWG\Response(response="200", description="Return phone details")
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="number",
     *     description="The id of the product"
     * )
     */
    public function showPhone($id, CacheInterface $cache)
    {
        return $cache->get('phone_show' . $id, function (ItemInterface $item) use ($id) {
            $item->expiresAfter(3600);

            return $this->repo->find($id);
        });
    }

    /**
     * @param Phone $phone
     * @param EntityManagerInterface $manager
     * @param ValidatorInterface $validator
     *
     * @return Phone
     * @throws Exception
     * @Rest\Post(
     *     path="/",
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
     *          @SWG\Property(property="Description", type="string")
     *     )
     * )
     * @SWG\Post(
     *     @SWG\Response(response="201", description="Return a new phone")
     * )
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     */
    public function addPhone(Phone $phone, EntityManagerInterface $manager, ValidatorInterface $validator): Phone
    {
        $errors = $validator->validate($phone);
        if (count($errors)) {
            throw new \RuntimeException($errors);
        }

        $manager->persist($phone);
        $manager->flush();

        return $phone;
    }

    /**
     * @param Phone $phone
     * @param EntityManagerInterface $manager
     * @Rest\Delete(
     *     path="/{id}",
     *     name="delete_phone",
     *     requirements={"id"="\d+"}
     * )
     * @Rest\View(statusCode= 204)
     * @SWG\Delete(
     *     @SWG\Response(response="204", description="Delete a specific phone")
     * )
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="number",
     *     description="The id of the phone"
     * )
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     */
    public function deletePhone(Phone $phone, EntityManagerInterface $manager): void
    {
        $manager->remove($phone);
        $manager->flush();
    }

    /**
     * @param EntityManagerInterface $manager
     * @param $id
     * @param Phone $phone
     * @param ValidatorInterface $validator
     * @return Phone|object|null
     * @Rest\Patch(
     *     path="/{id}",
     *     name="phone_update",
     *     requirements={"id"="\d+"}
     * )
     * @Rest\View(statusCode= 200)
     *
     * @SWG\Patch(
     *     @SWG\Response(response="200", description="Update phone")
     * )
     * @ParamConverter("phone", converter="fos_rest.request_body")
     * @SWG\Parameter(
     *     name="id",
     *     in="path",
     *     type="number",
     *     description="The id of the phone"
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
     *          @SWG\Property(property="Description", type="string")
     *     )
     * )
     * @Security("is_granted('ROLE_SUPER_ADMIN')")
     * @throws Exception
     */
    public function patchPhone(EntityManagerInterface $manager, $id, Phone $phone, ValidatorInterface $validator)
    {
        $result = $manager->getRepository(Phone::class)->findOneBy(['id' => $id]);

        $errors = $validator->validate($result);
        if (count($errors)) {
            throw new \RuntimeException('Invalid argument(s) detected');
        }

        $result->setPrice($phone->getPrice());
        $result->setDescription($phone->getDescription());
        $result->setName($phone->getName());

        $manager->persist($result);
        $manager->flush();

        return $result;
    }

}
