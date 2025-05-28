<?php

namespace App\Controller;

use App\Entity\Birthday;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Datetime\DateTimeImmutable;
use JMS\Serializer\SerializerInterface;

class BirthdayController extends AbstractController
{

    /**
     * @Route("/birthdays", name= "app_birthday_list", methods={"GET"})
     */
    public function index(SerializerInterface $serializer, EntityManagerInterface $em): Response
    {
        $birthdays = $em->getRepository(Birthday::class)->findAll();
        $json = $serializer->serialize($birthdays, 'json');
        return new Response($json, 200, [
            'Content-Type' => 'application/json'
        ]);
    }/*->findBy(['user' => $this->getUser()]);
     ;*/

    /**
     * @Route("/birthday/{id}", name= "get_birthday", methods={"GET"})
     */
    public function getBirthday(Birthday $birthday, SerializerInterface $serializer): Response
    {
        // if ($birthday->getUser() !== $this->getUser()) {
        //     return new JsonResponse(['error' => 'Accès interdit'], 403);
        // }

        $json = $serializer->serialize($birthday, 'json');
        return new Response($json, 200, ['Content-Type' => 'application/json']);
    }

    /**
     * @Route("/birthday", name= "create_birthday", methods={"POST"})
     */
    public function createBirthday(Request $request, EntityManagerInterface $em, SerializerInterface $serializer): Response
    {
        $birthday = $serializer->deserialize($request->getContent(), Birthday::class, 'json');
        $birthday->setUser($this->getUser());

        $em->persist($birthday);
        $em->flush();

        $json = $serializer->serialize($birthday, 'json');
        return new Response($json, 201, ['Content-Type' => 'application/json']);
    }

    /**
     * @Route("/birthday/{id}", name= "update_birthday", methods={"PUT"})
     */
    public function updateBirthday(Birthday $birthday, Request $request, EntityManagerInterface $em, SerializerInterface $serializer): Response
    {
        if ($birthday->getUser() !== $this->getUser()) {
            return new JsonResponse(['error' => 'Accès interdit'], 403);
        }

        $data = json_decode($request->getContent(), true);
        $birthday->setName($data['name'] ?? $birthday->getName());

        if (isset($data['date'])) {
            $birthday->setDate(new \DateTimeImmutable($data['date']));
        }

        $em->flush();

        $json = $serializer->serialize($birthday, 'json');
        return new Response($json, 200, ['Content-Type' => 'application/json']);
    }

    /**
     * @Route("/birthday/{id}", name= "delete_birthday", methods={"DELETE"})
     */
    public function deleteBirthday(Birthday $birthday, EntityManagerInterface $em): JsonResponse
    {
        if ($birthday->getUser() !== $this->getUser()) {
            return new JsonResponse(['error' => 'Accès interdit'], 403);
        }

        $em->remove($birthday);
        $em->flush();

        return new JsonResponse([
            'message' => "L'anniversaire a été supprimé avec succès.",
            '_links' => [
                'list' => ['href' => '/birthdays', 'method' => 'GET'],
                'create' => ['href' => '/birthday', 'method' => 'POST']
            ]
        ], 200);
    }
}