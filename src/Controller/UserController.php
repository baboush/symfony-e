<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Traits\ApiControllerTrait;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    use ApiControllerTrait;
    public function __construct(
        private EntityManagerInterface $em,
        private UserRepository $repo,
        private UserPasswordHasherInterface $passwordHasher,
    ) {
    }

    #[Route('/user', name: 'app_user')]
    public function index(): JsonResponse
    {
        return $this->json(
            $this->repo->findAll()
        );
    }

    #[Route('/user/signIn', name: 'app_user_sign_in', methods: ['POST'])]
    public function signIn(Request $request): JsonResponse
    {
        $user = new User();
        $form = $this->createForm(
            UserType::class,
            $user,
            ['csrf_protection' => false]
        );

        $data = json_decode($request->getContent(), true);
        $form->submit($data, false);

        if($form->isSubmitted() && $form->isValid()) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $form->get('password')->getData()));
            $this->em->persist($user);
            $this->em->flush();
            return $this->json($user, 201);
        }
        return $this->getFormErrors($form);
    }

    #[Route('/user/edit/{id}', name: 'app_user_edit', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, ?User $user, int $id): JsonResponse
    {
        if(!$user) {
            return $this->json(["message" => "User not found"], 404);
        }

        $form = $this->createForm(UserType::class, $user, ['csrf_protection' => false]);
        $data = json_decode($request->getContent(), true);
        $form->submit($data, false);
        if($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            return $this->json($user);
        }
        return $this->getFormErrors($form);
    }
}
