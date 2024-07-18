<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\Traits\ApiControllerTrait;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class UserController extends AbstractController
{
    use ApiControllerTrait;
    protected $em;
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        $this->em = $entityManager;
    }

    #[Route('/user', name: 'app_user')]
    public function index(): JsonResponse
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/UserController.php',
        ]);
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
            $this->em->persist($user);
            $this->em->flush();

            return $this->json($user, 201);
        }
        return $this->getFormErrors($form);
    }
}
