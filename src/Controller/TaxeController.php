<?php

namespace App\Controller;

use App\Entity\Taxe;
use App\Form\TaxeType;
use App\Form\Traits\ApiControllerTrait;
use App\Repository\TaxeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class TaxeController extends AbstractController
{
    use ApiControllerTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private TaxeRepository $repo
    ) {
    }

    #[Route('/api/taxe', name: 'app_taxe')]
    public function index(): JsonResponse
    {
        return $this->json($this->repo->findAll());
    }

    #[Route('/api/taxe/create', name: 'app_taxe_create', methods: ['POST'])]
    public function create(?Request $request): JsonResponse
    {
        $taxe = new Taxe();
        $form = $this->createForm(TaxeType::class, $taxe, ['csrf_protection' => false]);

        $data = json_decode($request->getContent(), true);
        $form->submit($data, false);

        if ($form->isSubmitted && $form->isValid()) {
            $this->em->persist($taxe);
            $this->em->flush();
            return $this->json($taxe);
        }
        return $this->getFormErrors($form);
    }

    #[Route('/api/taxe/edit/{id}', name: 'app_taxe_edit', methods: ['PUT', 'PATCH'])]
    public function edit(?Request $request, ?Taxe $taxe): JsonResponse
    {
        if (!$taxe) {
            return $this->json(["message" => "Taxe not found"], 404);
        }
        $form = $this->createForm(TaxeType::class, $taxe, ['csrf_protection' => false]);
        $data = json_decode($request->getContent(), true);
        $form->submit($data, false);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($taxe);
            $this->em->flush();
            return $this->json($taxe);
        }
        return $this->getFormErrors($form);
    }

    #[Route('/api/taxe/delete/{id}', name: 'app_taxe_delete', methods: ['DELETE'])]
    public function delete(?Taxe $taxe): JsonResponse
    {
        if (!$taxe) {
            return $this->json(["message" => "Taxe not found"], 404);
        }
        $this->em->remove($taxe);
        $this->em->flush();
        return $this->json(["message" => "Taxe is deleted successfull"], 204);
    }

    #[Route('/api/taxe/{id}', name: 'app_taxe_show')]
    public function show(?Taxe $taxe): JsonResponse
    {
        return $this->json($taxe);
    }
}
