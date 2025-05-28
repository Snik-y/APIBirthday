<?php

namespace App\Controller;

use App\Entity\Birthday;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManagerInterface;
use JMS\Serializer\SerializerInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="app_user")
     */
    /**
     * @Route("/user", name="app_user_index", methods={"GET"})
     */
    public function index(SerializerInterface $serializer, EntityManagerInterface $em): Response
    {
        $users = $em->getRepository(User::class)->findAll();

        $data = [];

        foreach ($users as $user) {
            $birthdays = [];
            foreach ($user->getBirthdays() as $birthday) {
                $birthdays[] = [
                    'id' => $birthday->getId(),
                    'title' => $birthday->getName(),
                    'date' => $birthday->getDate()->format('Y-m-d'),
                ];
            }

            $data[] = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'birthdays' => $birthdays,
            ];
        }

        return new JsonResponse($data, 200);
    }

    /**
     * @Route("/user", name="app_user_new", methods={"POST"})
     */
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setEmail($data['email']);
        $user->setRoles($data['roles'] ?? ['ROLE_USER']);
        $user->setPassword($data['password']);
        // $user->setName($data['name']);
        // $user->setBirthday(new \DateTime($data['birthday']));

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json(['message' => 'User created successfully'], Response::HTTP_CREATED);
    }

    /**
     * @Route("/user/{id}", name="app_user_show", methods={"GET"})
     */
    public function show(User $user): JsonResponse
    {
        $data = [
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'password' => $user->getPassword(),
            'roles' => $user->getRoles(),
            // 'birthday' => $user->getBirthday()->format('Y-m-d'),
        ];

        return $this->json($data);
    }

    /**
     * @Route("/user/{id}", name="app_user_edit", methods={"PUT"})
     */
    public function edit(Request $request, User $user, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // $user->setName($data['name']);
        $user->setEmail($data['email']);
        $user->setPassword($data['password'] ?? $user->getPassword());
        $user->setRoles($data['roles'] ?? $user->getRoles());
        // $user->setBirthday(new \DateTime($data['birthday']));

        $entityManager->flush();

        return $this->json(['message' => 'User updated successfully']);
    }

    /**
     * @Route("/user/{id}", name="app_user_delete", methods={"DELETE"})
     */
    public function delete(User $user, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($user);
        $entityManager->flush();

        return $this->json(['message' => 'User deleted successfully']);
    }
}