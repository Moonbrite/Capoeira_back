<?php

namespace App\Controller;

use App\Document\Ressources;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class RessourcesController extends AbstractController
{

    /**
     * @throws MongoDBException
     * @throws Throwable
     */
    #[Route('/ressources', name: 'app_ressources_add', methods: ['POST'])]
    public function addRessources(Request $request, DocumentManager $dm): JsonResponse
    {

        $data = json_decode($request->getContent(), true);

        $ressources = new Ressources();
        $ressources->setTitle($data['title']);
        $ressources->setUrl($data['url']);
        $ressources->setType($data['type']);

        // Persister et enregistrer dans MongoDB
        $dm->persist($ressources);
        $dm->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Ressources ajouté avec succès.',
            'title' => $ressources->getTitle(),
        ],Response::HTTP_OK);
    }

    #[Route('/ressources/{_id}', name: 'app_ressources_find', methods: ['GET'])]
    public function findRessources(Request $request, DocumentManager $dm, string $_id): JsonResponse
    {
        $ressources = $dm->find(Ressources::class, $_id);

        if (!$ressources) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Ressources introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }

        $ressourcesJson = [
            "id" => $ressources->getId(),
            "title" => $ressources->getTitle(),
            "url" => $ressources->getUrl(),
            "type" => $ressources->getType()
        ];


        return new JsonResponse([
            'success' => true,
            'message' => 'Ressources trouvé avec succès.',
            'ressources' => $ressourcesJson
        ],Response::HTTP_OK);

    }

    #[Route('/ressources/type/{type}', name: 'app_ressources_find', methods: ['GET'])]
    public function findRessourcesbyType(Request $request, DocumentManager $dm, string $type): JsonResponse
    {

        $ressources = $dm->getRepository(Ressources::class)->findBy(['type' => $type]);

        if (!$ressources) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Ressources introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }

        $ressourcesJson = array_map(function ($ressource) {
            return [
                "id" => $ressource->getId(),
                "title" => $ressource->getTitle(),
                "url" => $ressource->getUrl(),
                "type" => $ressource->getType()
            ];
        }, $ressources);


        return new JsonResponse([
            'success' => true,
            'message' => 'Ressources trouvé avec succès.',
            'ressources' => $ressourcesJson,
            'count' => sizeof($ressourcesJson),
        ],Response::HTTP_OK);

    }

    /**
     * @throws MongoDBException
     * @throws Throwable
     */
    #[Route('/ressources/{_id}', name: 'app_ressources_delete', methods: ['DELETE'])]
    public function deleteRessources(Request $request, DocumentManager $dm, string $_id): JsonResponse
    {
        $ressources = $dm->find(Ressources::class, $_id);

        if (!$ressources) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Ressources introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }

        $dm->remove($ressources);
        $dm->flush();


        return new JsonResponse([
            'success' => true,
            'message' => 'Ressources supprimé avec succès.',
        ],Response::HTTP_OK);

    }

    #[Route('/ressources', name: 'app_ressources_find_all', methods: ['GET'])]
    public function findAllRessources(Request $request, DocumentManager $dm): JsonResponse
    {
        $ressources = $dm->getRepository(Ressources::class)->findAll();

        if (!$ressources) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Ressources introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }

        $ressourcesJson = array_map(function ($ressource) {
            return [
                "id" => $ressource->getId(),
                "title" => $ressource->getTitle(),
                "url" => $ressource->getUrl(),
                "type" => $ressource->getType()
            ];
        }, $ressources);

        return new JsonResponse([
            'success' => true,
            'message' => 'Ressources trouvé avec succès.',
            'ressources' => $ressourcesJson,
            'count'=> count($ressourcesJson)
        ],Response::HTTP_OK);

    }


    /**
     * @throws MongoDBException
     * @throws Throwable
     */
    #[Route('/ressources/{_id}', name: 'app_ressources_update', methods: ['PATCH'])]
    public function updateRessources(Request $request, DocumentManager $dm, string $_id): JsonResponse
    {

        $ressources = $dm->find(Ressources::class, $_id);

        if (!$ressources) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Ressource introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Données invalides ou manquantes.'
            ], Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['title'])) {
            $ressources->setTitle($data['title']);
        }

        if (isset($data['url'])) {
            $ressources->setUrl($data['url']);
        }

        if (isset($data['type'])) {
            $ressources->setType($data['type']);
        }

        $dm->flush();

        $ressourcesJson = [
            "id" => $ressources->getId(),
            "title" => $ressources->getTitle(),
            "url" => $ressources->getUrl(),
            "type" => $ressources->getType()
        ];

        return new JsonResponse([
            'success' => true,
            'message' => 'Ressource mise à jour avec succès.',
            'ressources' => $ressourcesJson
        ], Response::HTTP_OK);
    }


    #[Route('/ressources', name: 'app_ressources_update_all', methods: ['PATCH'])]
    public function updateAllRessources(Request $request, DocumentManager $dm): JsonResponse
    {

        $ressources = $dm->getRepository(Ressources::class)->findAll();

        if (!$ressources) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Ressource introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Données invalides ou manquantes.'
            ], Response::HTTP_BAD_REQUEST);
        }

        foreach ($ressources as $ressource) {
            if (isset($data['title'])) {
                $ressource->setTitle($data['title']);
            }

            if (isset($data['url'])) {
                $ressource->setUrl($data['url']);
            }

            if (isset($data['type'])) {
                $ressource->setType($data['type']);
            }
            $dm->flush();
        }


        return new JsonResponse([
            'success' => true,
            'message' => 'Toute les Ressources ont eté mise à jour avec succès.',
        ], Response::HTTP_OK);
    }



}