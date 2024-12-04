<?php

namespace App\Controller;

use App\Document\Event;
use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class UserController extends AbstractController
{
    /**
     * @throws Throwable
     * @throws MongoDBException
     */
    #[Route('/users', name: 'app_users_add', methods: ['POST'])]
    public function addUsers(Request $request, DocumentManager $dm): JsonResponse
    {

        $data = json_decode($request->getContent(), true);

        $users = new User();
        $users->setName($data['name']);
        $users->setEmail($data['email']);
        $users->setPassword($data['password']);
        $users->setSchoolId($data['school_id']);
        $users->setRefNum($data['refNum']);


        // Persister et enregistrer dans MongoDB
        $dm->persist($users);
        $dm->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'User ajouté avec succès.',
            'title' => $users->getName(),
        ],Response::HTTP_OK);
    }

    #[Route('/users/{_id}', name: 'app_users_find', methods: ['GET'])]
    public function findUsers(Request $request, DocumentManager $dm, string $_id): JsonResponse
    {
        $users = $dm->find(User::class, $_id);

        if (!$users) {
            return new JsonResponse([
                'success' => false,
                'message' => 'User introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }


        $usersJson = [
            "id" => $users->getId(),
            "name" => $users->getName(),
            "email" => $users->getEmail(),
            "password" => $users->getPassword(),
            "school_id" => $users->getSchoolId(),
            "refNum" => $users->getRefNum()
        ];


        return new JsonResponse([
            'success' => true,
            'message' => 'User trouvé avec succès.',
            'user' => $usersJson
        ],Response::HTTP_OK);

    }

    /**
     * @throws MongoDBException
     * @throws Throwable
     */
    #[Route('/users/{_id}', name: 'app_users_delete', methods: ['DELETE'])]
    public function deleteUsers(Request $request, DocumentManager $dm, string $_id): JsonResponse
    {
        $users = $dm->find(User::class, $_id);

        if (!$users) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Event introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }

        $dm->remove($users);
        $dm->flush();


        return new JsonResponse([
            'success' => true,
            'message' => 'User supprimé avec succès.',
        ],Response::HTTP_OK);

    }

    #[Route('/users', name: 'app_users_find_all', methods: ['GET'])]
    public function findAllUsers(Request $request, DocumentManager $dm): JsonResponse
    {
        $users = $dm->getRepository(User::class)->findAll();

        if (!$users) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Users introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }

        $usersJson = array_map(function ($user) {
            return [
                "id" => $user->getId(),
                "name" => $user->getName(),
                "email" => $user->getEmail(),
                "password" => $user->getPassword(),
                "school_id" => $user->getSchoolId(),
                "refNum" => $user->getRefNum()
            ];
        }, $users);

        return new JsonResponse([
            'success' => true,
            'message' => 'Events trouvé avec succès.',
            'discussions' => $usersJson,
            'count' => count($usersJson)
        ],Response::HTTP_OK);

    }

    #[Route('/users/{_id}', name: 'app_users_update', methods: ['PATCH'])]
    public function updateUsers(Request $request, DocumentManager $dm, string $_id): JsonResponse
    {

        $users = $dm->find(User::class, $_id);

        if (!$users) {
            return new JsonResponse([
                'success' => false,
                'message' => 'User introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Données invalides ou manquantes.'
            ], Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['name'])) {
            $users->setName($data['name']);
        }

        if (isset($data['email'])) {
            $users->setEmail($data['email']);
        }

        if (isset($data['category'])) {
            $users->setCategory($data['category']);
        }

        if (isset($data['password'])) {
            $users->setPassword($data['password']);
        }

        if (isset($data['school_id'])) {
            $users->setSchoolId($data['school_id']);
        }

        if (isset($data['refNum'])) {
            $users->setRefNum($data['refNum']);
        }

        $dm->flush();

        $usersJson = [
            "id" => $users->getId(),
            "name" => $users->getName(),
            "email" => $users->getEmail(),
            "password" => $users->getPassword(),
            "school_id" => $users->getSchoolId(),
            "refNum" => $users->getRefNum()
        ];

        return new JsonResponse([
            'success' => true,
            'message' => 'User mise à jour avec succès.',
            'event' => $usersJson
        ], Response::HTTP_OK);
    }

    /**
     * @throws MongoDBException
     * @throws Throwable
     */
    #[Route('/users', name: 'app_events_update_all', methods: ['PATCH'])]
    public function updateAllEvents(Request $request, DocumentManager $dm): JsonResponse
    {

        $users = $dm->getRepository(User::class)->findAll();

        if (!$users) {
            return new JsonResponse([
                'success' => false,
                'message' => 'User introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Données invalides ou manquantes.'
            ], Response::HTTP_BAD_REQUEST);
        }

        foreach ($users as $user) {
            if (isset($data['name'])) {
                $user->setName($data['name']);
            }

            if (isset($data['email'])) {
                $user->setEmail($data['email']);
            }

            if (isset($data['category'])) {
                $user->setCategory($data['category']);
            }

            if (isset($data['password'])) {
                $user->setPassword($data['password']);
            }

            if (isset($data['school_id'])) {
                $user->setSchoolId($data['school_id']);
            }

            if (isset($data['refNum'])) {
                $user->setRefNum($data['refNum']);
            }
            $dm->flush();
        }


        return new JsonResponse([
            'success' => true,
            'message' => 'Toute les users ont eté mise à jour avec succès.',
        ], Response::HTTP_OK);
    }


}