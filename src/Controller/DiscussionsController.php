<?php

namespace App\Controller;

use App\Document\Discussions;
use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class DiscussionsController extends AbstractController
{

    /**
     * @throws Throwable
     * @throws MongoDBException
     */
    #[Route('/discussions', name: 'app_discussions_add', methods: ['POST'])]
    public function addDiscussions(Request $request, DocumentManager $dm): JsonResponse
    {

        $data = json_decode($request->getContent(), true);

        $user = $dm->find(User::class, $data['author_id']);


        $discussions = new Discussions();
        $discussions->setSubject($data['subject']);
        $discussions->setCreatedAt($data['createdAt']);
        $discussions->setDescription($data['description']);
        $discussions->setAuthorId($user);

        // Persister et enregistrer dans MongoDB
        $dm->persist($discussions);
        $dm->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Discussion ajouté avec succès.',
            'title' => $discussions->getSubject(),
        ],Response::HTTP_OK);
    }

    #[Route('/discussions/{_id}', name: 'app_discussions_find', methods: ['GET'])]
    public function findDiscussions(Request $request, DocumentManager $dm, string $_id): JsonResponse
    {
        $discussions = $dm->find(Discussions::class, $_id);

        if (!$discussions) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Discussions introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }


        $DiscussionsJson = [
            "id" => $discussions->getId(),
            "subject" => $discussions->getSubject(),
            "description" => $discussions->getDescription(),
            "author_id" => $discussions->getAuthorId(),
            "createdAt" => $discussions->getCreatedAt()
        ];


        return new JsonResponse([
            'success' => true,
            'message' => 'Discussions trouvé avec succès.',
            'discussions' => $DiscussionsJson
        ],Response::HTTP_OK);

    }

    #[Route('/discussions/date/{date}', name: 'app_discussions_find_date', methods: ['GET'])]
    public function findDiscussionsbyDate(Request $request, DocumentManager $dm, int $date): JsonResponse
    {

        $dateEnd = $date + 86400;


        $discussions = $dm->getRepository(Discussions::class)->findBy(['createdAt' =>  [
            '$gte' => $date,
            '$lt' => $dateEnd
        ]]);


        if (!$discussions) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Discussions introuvable avec cette date.'
            ], Response::HTTP_NOT_FOUND);
        }

        $discussionsJson = array_map(function ($discussion) {
            return [
                "id" => $discussion->getId(),
                "subject" => $discussion->getSubject(),
                "description" => $discussion->getDescription(),
                "author_id" => $discussion->getAuthorId(),
                "createdAt" => $discussion->getCreatedAt()
            ];
        }, $discussions);


        return new JsonResponse([
            'success' => true,
            'message' => 'Ressources trouvé avec succès.',
            'discussions' => $discussionsJson,
            'count' => sizeof($discussionsJson),
        ],Response::HTTP_OK);

    }

    /**
     * @throws Throwable
     * @throws MongoDBException
     */
    #[Route('/discussions/{_id}', name: 'app_discussions_delete', methods: ['DELETE'])]
    public function deleteDiscussions(Request $request, DocumentManager $dm, string $_id): JsonResponse
    {
        $discussions = $dm->find(Discussions::class, $_id);

        if (!$discussions) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Discussions introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }

        $dm->remove($discussions);
        $dm->flush();


        return new JsonResponse([
            'success' => true,
            'message' => 'Discussions supprimé avec succès.',
        ],Response::HTTP_OK);

    }

    #[Route('/discussions', name: 'app_discussions_find_all', methods: ['GET'])]
    public function findAllDiscussions(Request $request, DocumentManager $dm): JsonResponse
    {
        $discussions = $dm->getRepository(Discussions::class)->findAll();

        if (!$discussions) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Discussions introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }

        $discussionsJson = array_map(function ($discussion) {
            return [
                "id" => $discussion->getId(),
                "subject" => $discussion->getSubject(),
                "description" => $discussion->getDescription(),
                "author_id" => $discussion->getAuthorId(),
                "createdAt" => $discussion->getCreatedAt()
            ];
        }, $discussions);

        return new JsonResponse([
            'success' => true,
            'message' => 'Discussions trouvé avec succès.',
            'discussions' => $discussionsJson,
            'count' => count($discussions)
        ],Response::HTTP_OK);

    }

    /**
     * @throws MongoDBException
     * @throws Throwable
     */
    #[Route('/discussions/{_id}', name: 'app_discussions_update', methods: ['PATCH'])]
    public function updateDiscussions(Request $request, DocumentManager $dm, string $_id): JsonResponse
    {

        $discussions = $dm->find(Discussions::class, $_id);

        if (!$discussions) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Discussions introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Données invalides ou manquantes.'
            ], Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['description'])) {
            $discussions->setDescription($data['description']);
        }

        if (isset($data['author_id'])) {
            $discussions->setAuthorId($data['author_id']);
        }

        if (isset($data['subject'])) {
            $discussions->setSubject($data['subject']);
        }

        if (isset($data['createdAt'])) {
            $discussions->setCreatedAt($data['createdAt']);
        }


        $dm->flush();

        $discussionsJson = [
            "id" => $discussions->getId(),
            "subject" => $discussions->getSubject(),
            "description" => $discussions->getDescription(),
            "author_id" => $discussions->getAuthorId(),
            "createdAt" => $discussions->getCreatedAt()
        ];

        return new JsonResponse([
            'success' => true,
            'message' => 'Discussions mise à jour avec succès.',
            'discusion' => $discussionsJson
        ], Response::HTTP_OK);
    }

    /**
     * @throws MongoDBException
     * @throws Throwable
     */
    #[Route('/discussions', name: 'app_discussions_update_all', methods: ['PATCH'])]
    public function updateAllDiscussions(Request $request, DocumentManager $dm): JsonResponse
    {

        $discussions = $dm->getRepository(Discussions::class)->findAll();

        if (!$discussions) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Discussions introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Données invalides ou manquantes.'
            ], Response::HTTP_BAD_REQUEST);
        }

        foreach ($discussions as $discussion) {
            if (isset($data['description'])) {
                $discussion->setDescription($data['description']);
            }

            if (isset($data['author_id'])) {
                $discussion->setAuthorId($data['author_id']);
            }

            if (isset($data['subject'])) {
                $discussion->setSubject($data['subject']);
            }

            if (isset($data['createdAt'])) {
                $discussion->setCreatedAt($data['createdAt']);
            }
            $dm->flush();
        }


        return new JsonResponse([
            'success' => true,
            'message' => 'Toute les Discussions ont eté mise à jour avec succès.',
        ], Response::HTTP_OK);
    }


}