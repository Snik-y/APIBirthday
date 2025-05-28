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
    public function getBirthday(SerializerInterface $serializer, EntityManagerInterface $em, Request $request): Response
    {
        // if ($birthday->getUser() !== $this->getUser()) {
        //     return new JsonResponse(['error' => 'Accès interdit'], 403);
        // }
        $birthday = $em->getRepository(Birthday::class)->find($request->get('id'));
        $json = $serializer->serialize($birthday, 'json');
        if (!$birthday) {
            throw $this->createNotFoundException('Anniversaire non trouvé.');
        }

        return $this->json([
            'id' => $birthday->getId(),
            'name' => $birthday->getName(),
            'date' => $birthday->getDate()->format('Y-m-d'),
        ]);
    }

    /**
     * @Route("/birthday", name= "create_birthday", methods={"POST"})
     */
    public function createBirthday(Request $request, EntityManagerInterface $em, SerializerInterface $serializer): Response
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name']) || !isset($data['date'])) {
            return $this->json(['error' => 'Champs "name" et "date" requis.'], 400);
        }

        try {
            $birthday = new Birthday();
            $birthday->setName($data['name']);
            $birthday->setDate(new \DateTimeImmutable($data['date']));
        } catch (\Exception $e) {
            return $this->json(['error' => 'Date invalide, format attendu: Y-m-d.'], 400);
        }

        $em->persist($birthday);
        $em->flush();

        return $this->json([
            'message' => 'Anniversaire créé avec succès.',
            'birthday' => [
                'id' => $birthday->getId(),
                'name' => $birthday->getName(),
                'date' => $birthday->getDate()->format('Y-m-d'),
            ]
        ], 201);
    }

    /**
     * @Route("/birthday/{id}", name= "update_birthday", methods={"PUT"})
     */
    public function updateBirthday(Request $request, EntityManagerInterface $em, SerializerInterface $serializer): Response
    {
        $birthday = $em->getRepository(Birthday::class)->find($request->get('id'));

        if (!$birthday) {
            return $this->json(['error' => 'Anniversaire non trouvé.'], 404);
        }

        // if ($birthday->getUser() !== $this->getUser()) {
        //     return $this->json(['error' => 'Accès interdit'], 403);
        // }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $birthday->setName($data['name']);
        }

        if (isset($data['date'])) {
            try {
                $birthday->setDate(new \DateTimeImmutable($data['date']));
            } catch (\Exception $e) {
                return $this->json(['error' => 'Date invalide, format attendu : Y-m-d.'], 400);
            }
        }

        $em->flush();

        return $this->json([
            'message' => 'Anniversaire mis à jour avec succès.',
            'birthday' => [
                'id' => $birthday->getId(),
                'name' => $birthday->getName(),
                'date' => $birthday->getDate()->format('Y-m-d'),
            ]
        ]);
    }

    /**
     * @Route("/birthday/{id}", name= "delete_birthday", methods={"DELETE"})
     */
    public function deleteBirthday(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $birthday = $em->getRepository(Birthday::class)->find($request->get('id'));

        if (!$birthday) {
            return $this->json(['error' => 'Anniversaire non trouvé.'], 404);
        }

        // if ($birthday->getUser() !== $this->getUser()) {
        //     return $this->json(['error' => 'Accès interdit'], 403);
        // }

        $em->remove($birthday);
        $em->flush();

        return $this->json([
            'message' => "L'anniversaire a été supprimé avec succès.",
            '_links' => [
                'list' => ['href' => '/birthdays', 'method' => 'GET'],
                'create' => ['href' => '/birthday', 'method' => 'POST']
            ]
        ]);
    }
}