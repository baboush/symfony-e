<?php

namespace App\Controller;

use App\Entity\Gender;
use App\Form\GenderType;
use App\Form\Traits\ApiControllerTrait;
use App\Repository\GenderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class GenderController extends AbstractController
{
    use ApiControllerTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private GenderRepository $repo,
    ) {

    }
    #[Route('/api/gender', name: 'app_gender')]
    public function index(): JsonResponse
    {
        $gender = $this->repo->findAll();
        return $this->json($gender);
    }

    #[Route('/api/gender/create', name: 'app_gender_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $gender = new Gender();
        $form = this->createForm(GenderType::class, $gender, [csrf_protection => false]);
        $data = json_decode($request->getContent(), true);

        $form->submit($data, false);
        if($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($gender);
            $this->em->flush();
            return $this->json($gender, 201);
        }
        return $this->getFormErrors($form);
    }

    #[Route('/api/gender/edit/{id}', name: 'app_gender_edit', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, ?Gender $gender, int $id): JsonResponse
    {
        if(!$gender) {
            return $this->json(["message" => "Gender not found"], 404);
        }
        $form = $this->createForm(GenderType::class, $gender, ['csrf_protection' => false]);
        $data = json_decode($request->getContent(), true);
        $form->submit($data, false);
        if($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            return $this->json($gender, 200);
        }
        return $this->getFormErrors($form);
    }
}
