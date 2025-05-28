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
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
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
    public function new(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $user = new User();
        $user->setEmail($data['email']);
        $user->setRoles($data['roles'] ?? ['ROLE_USER']);

        $hashedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->json(['message' => 'User created successfully'], Response::HTTP_CREATED);
    }

    /**
     * @Route("/user/{id}", name="app_user_show", methods={"GET"})
     */
    public function show(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $user = $entityManager->getRepository(User::class)->find($request->get('id'));

        if (!$user) {
            return $this->json(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        // Check if the user is the owner of the resource
        // if ($user->getId() !== $this->getUser()->getId()) {
        //     return $this->json(['error' => 'Access denied'], Response::HTTP_FORBIDDEN);
        // }

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
            // 'name' => $user->getName(),
            // 'birthday' => $user->getBirthday() ? $user->getBirthday()->format('Y-m-d') : null,
        ]);
    }
}