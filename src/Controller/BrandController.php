<?php

namespace App\Controller;

use App\Entity\Brand;
use App\Form\BrandType;
use App\Form\Traits\ApiControllerTrait;
use App\Repository\BrandRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class BrandController extends AbstractController
{
    use ApiControllerTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private BrandRepository $repo,
    ) {

    }

    #[Route('/api/brand', name: 'app_brand')]
    public function index(): JsonResponse
    {
        return $this->json($this->repo->findAll());
    }

    #[Route('/api/brand/create', name: 'app_brand_create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $brand = new Brand();
        $form = $this->createForm(BrandType::class, $brand, ['csrf_protection' => false]);
        $data = json_decode($request->getContent(), true);
        $form->submit($data, false);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($brand);
            $this->em->flush();
            return $this->json($brand, 201);
        }

        return $this->getFormErrors($form);
    }

    #[Route('/api/brand/edit/{id}', name: 'app_brand_edit', methods: ['PUT', 'PATCH'])]
    public function update(Request $request, ?Brand $brand): JsonResponse
    {
        if (!$brand) {
            return $this->json(["message" => "Brand not found"], 404);
        }
        $form = $this->createForm(BrandType::class, $brand, ['csrf_protection' => false]);
        $data = json_decode($request->getContent(), true);
        $form->submit($data, false);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            return $this->json($brand, 200);
        }
        return $this->getFormErrors($form);
    }
}
