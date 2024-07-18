<?php

namespace App\Controller;

use App\Entity\Model;
use App\Form\ModelType;
use App\Form\Traits\ApiControllerTrait;
use App\Repository\ModelRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

class ModelController extends AbstractController
{
    use ApiControllerTrait;

    public function __construct(
        private EntityManagerInterface $em,
        private ModelRepository $repo
    ) {

    }
    #[Route('/api/model', name: 'app_model')]
    public function index(): JsonResponse
    {
        return this->json($this->repo->findAll());
    }

    #[Route('/api/model/create', name: 'app_model_create', methods: ['POST'])]
    public function create(?Request $request): JsonResponse
    {
        $model = new Model();
        $form = $this->createForm(ModelType::class, $model, [csrf_protection => false]);

        $data = json_decode($request->getContent(), true);
        $form->submit($data, false);

        if($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($model);
            $this->em->flush();
            return $this->json($model);
        }
        return $this->getFormErrors($form);
    }

    #[Route('/api/model/edit/{id}', name: 'app_model_edit', methods: ['PUT', 'PATCH'])]
    public function edit(?Request $request, ?Model $model): JsonResponse
    {
        if(!$model) {
            return $this->json(["message" => "Model not found"], 404);
        }
        $form = $this->createForm(ModelType::class, $model, ['csrf_protection' => false]);
        $data = json_decode($request->getContent(), true);
        $form->submit($data, false);
        if($form->isSubmitted() && $form->isValid()) {
            $this->em->flush();
            return $this->json($model);
        }
        return $this->getFormErrors($form);
    }

    #[Route('/api/model/delete/{id}', name: 'app_model_delete', methods: ['DELETE'])]
    public function delete(?Model $model): JsonResponse
    {
        if(!$model) {
            return $this->json(["message" => "Model not found"], 404);
        }
        $this->em->remove($model);
        $this->em->flush();
        return $this->json(["message" => "Model deleted"]);
    }

    #[Route('/api/model/{id}', name: 'app_model_show')]
    public function show(?Model $model): JsonResponse
    {
        return $this->json($model);
    }
}
