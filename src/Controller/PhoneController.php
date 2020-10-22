<?php

namespace App\Controller;

use App\Entity\Phone;
use App\Repository\PhoneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @Route("/api", name="phone")
 */
class PhoneController extends AbstractController
{

    /**
     * @Route("/phones/{id}", name="show_phone", methods={"GET"})
     * @Groups({"show"})
     * @param Phone $phone
     * @param PhoneRepository $phoneRepository
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function show(Phone $phone, PhoneRepository $phoneRepository, SerializerInterface $serializer): Response
    {
        $phone = $phoneRepository->find($phone->getId());
        $data = $serializer->serialize($phone, 'json', [
            'groups' => ['show']
        ]);

        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @Route("/phones/{page<\d+>?1}", name="list_phone", methods={"GET"})
     * @param Request $request
     * @param PhoneRepository $phoneRepository
     * @param SerializerInterface $serializer
     * @return Response
     */
    public function index(Request $request,PhoneRepository $phoneRepository, SerializerInterface $serializer): Response
    {
        $page = $request->query->get('page');

        if($page === null || $page < 1) {
            $page = 1;
        }

        $phones = $phoneRepository->findAllPhones($page, 10);
        $data = $serializer->serialize($phones, 'json', [
            'groups' => ['list']
        ]);

        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @Route("/phones", name="add_phone", methods={"POST"})
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param EntityManagerInterface $entityManager
     * @param ValidatorInterface $validator
     * @return JsonResponse|Response
     */
    public function new(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, ValidatorInterface $validator)
    {
        $phone = $serializer->deserialize($request->getContent(), Phone::class, 'json');
        $errors = $validator->validate($phone);
        if(count($errors)) {
            $errors = $serializer->serialize($errors, 'json');
            return new Response($errors, 500, [
                'Content-Type' => 'application/json'
            ]);
        }
        $entityManager->persist($phone);
        $entityManager->flush();
        $data = [
            'status' => 201,
            'message' => 'Le téléphone a bien été ajouté'
        ];
        return new JsonResponse($data, 201);
    }

    /**
     * @Route("/phones/{id}", name="update_phone", methods={"PUT"})
     * @param Request $request
     * @param SerializerInterface $serializer
     * @param Phone $phone
     * @param ValidatorInterface $validator
     * @param EntityManagerInterface $entityManager
     * @return JsonResponse|Response
     */
    public function update(Request $request, SerializerInterface $serializer, Phone $phone, ValidatorInterface $validator, EntityManagerInterface $entityManager)
    {
        $phoneUpdate = $entityManager->getRepository(Phone::class)->find($phone->getId());
        $data = json_decode($request->getContent());
        foreach ($data as $key => $value){
            if($key && !empty($value)) {
                $name = ucfirst($key);
                $setter = 'set'.$name;
                $phoneUpdate->$setter($value);
            }
        }
        $errors = $validator->validate($phoneUpdate);
        if(count($errors)) {
            $errors = $serializer->serialize($errors, 'json');
            return new Response($errors, 500, [
                'Content-Type' => 'application/json'
            ]);
        }
        $entityManager->flush();
        $data = [
            'status' => 200,
            'message' => 'Le téléphone a bien été mis à jour'
        ];

        return new JsonResponse($data);
    }

    /**
     * @Route("/phones/{id}", name="delete_phone", methods={"DELETE"})
     * @param Phone $phone
     * @param EntityManagerInterface $entityManager
     * @return Response
     */
    public function delete(Phone $phone, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($phone);
        $entityManager->flush();

        return new Response(null, 204);
    }
}
