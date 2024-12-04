<?php

namespace App\Controller;

use App\Document\Event;
use App\Document\School;
use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class EventController extends AbstractController
{

    /**
     * @throws Throwable
     * @throws MongoDBException
     */
    #[Route('/events', name: 'app_events_add', methods: ['POST'])]
    public function addEvents(Request $request, DocumentManager $dm): JsonResponse
    {

        $data = json_decode($request->getContent(), true);

        $schools = $dm->find(School::class, $data['school_id']);

        $events = new Event();
        $events->setTitle($data['title']);
        $events->setDescription($data['description']);
        $events->setCategory($data['category']);
        $events->setPublicEvent($data['public_event']);
        $events->setCity($data['city']);
        $events->setSchoolId($schools);
        $events->setStartDate($data['start_date']);
        $events->setEndDate($data['end_date']);
        $events->setImageUrl($data['image_url']);

        // Persister et enregistrer dans MongoDB
        $dm->persist($events);
        $dm->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Event ajouté avec succès.',
            'title' => $events->getTitle(),
        ],Response::HTTP_OK);
    }

    #[Route('/events/{_id}', name: 'app_events_find', methods: ['GET'])]
    public function findEvents(Request $request, DocumentManager $dm, string $_id): JsonResponse
    {
        $events = $dm->find(Event::class, $_id);

        if (!$events) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Event introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }


        $EventsJson = [
            "id" => $events->getId(),
            "title" => $events->getTitle(),
            "description" => $events->getDescription(),
            "category" => $events->getCategory(),
            "public_event" => $events->getPublicEvent(),
            "city" => $events->getCity(),
            "school_id" => $events->getSchoolId(),
            "start_date" => $events->getStartDate(),
            "end_date" => $events->getEndDate(),
            "image_url" => $events->getImageUrl()
        ];


        return new JsonResponse([
            'success' => true,
            'message' => 'Event trouvé avec succès.',
            'envent' => $EventsJson
        ],Response::HTTP_OK);

    }

    /**
     * @throws MongoDBException
     * @throws Throwable
     */
    #[Route('/events/{_id}', name: 'app_events_delete', methods: ['DELETE'])]
    public function deleteEvents(Request $request, DocumentManager $dm, string $_id): JsonResponse
    {
        $events = $dm->find(Event::class, $_id);

        if (!$events) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Event introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }

        $dm->remove($events);
        $dm->flush();


        return new JsonResponse([
            'success' => true,
            'message' => 'Event supprimé avec succès.',
        ],Response::HTTP_OK);

    }

    #[Route('/events', name: 'app_events_find_all', methods: ['GET'])]
    public function findAllEvents(Request $request, DocumentManager $dm): JsonResponse
    {
        $events = $dm->getRepository(Event::class)->findAll();

        if (!$events) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Event introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }

        $eventsJson = array_map(function ($event) {
            return [
                "id" => $event->getId(),
                "title" => $event->getTitle(),
                "description" => $event->getDescription(),
                "category" => $event->getCategory(),
                "public_event" => $event->getPublicEvent(),
                "city" => $event->getCity(),
                "school_id" => $event->getSchoolId(),
                "start_date" => $event->getStartDate(),
                "end_date" => $event->getEndDate(),
                "image_url" => $event->getImageUrl()
            ];
        }, $events);

        return new JsonResponse([
            'success' => true,
            'message' => 'Events trouvé avec succès.',
            'discussions' => $eventsJson,
            'count' => count($eventsJson)
        ],Response::HTTP_OK);

    }

    /**
     * @throws MongoDBException
     * @throws Throwable
     */
    #[Route('/events/{_id}', name: 'app_events_update', methods: ['PATCH'])]
    public function updateEvents(Request $request, DocumentManager $dm, string $_id): JsonResponse
    {

        $events = $dm->find(Event::class, $_id);

        if (!$events) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Event introuvable.'
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
            $events->setTitle($data['title']);
        }

        if (isset($data['description'])) {
            $events->setDescription($data['description']);
        }

        if (isset($data['category'])) {
            $events->setCategory($data['category']);
        }

        if (isset($data['public_event'])) {
            $events->setPublicEvent($data['public_event']);
        }
        if (isset($data['city'])) {
            $events->setCity($data['city']);
        }
        if (isset($data['school_id'])) {
            $events->setSchoolId($data['school_id']);
        }
        if (isset($data['start_date'])) {
            $events->setStartDate($data['start_date']);
        }
        if (isset($data['end_date'])) {
            $events->setEndDate($data['end_date']);
        }
        if (isset($data['image_url'])) {
            $events->setImageUrl($data['image_url']);
        }


        $dm->flush();

        $eventsJson = [
            "id" => $events->getId(),
            "title" => $events->getTitle(),
            "description" => $events->getDescription(),
            "category" => $events->getCategory(),
            "public_event" => $events->getPublicEvent(),
            "city" => $events->getCity(),
            "school_id" => $events->getSchoolId(),
            "start_date" => $events->getStartDate(),
            "end_date" => $events->getEndDate(),
            "image_url" => $events->getImageUrl()
        ];

        return new JsonResponse([
            'success' => true,
            'message' => 'Event mise à jour avec succès.',
            'event' => $eventsJson
        ], Response::HTTP_OK);
    }

    /**
     * @throws MongoDBException
     * @throws Throwable
     */
    #[Route('/events', name: 'app_events_update_all', methods: ['PATCH'])]
    public function updateAllEvents(Request $request, DocumentManager $dm): JsonResponse
    {

        $events = $dm->getRepository(Event::class)->findAll();

        if (!$events) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Event introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Données invalides ou manquantes.'
            ], Response::HTTP_BAD_REQUEST);
        }

        foreach ($events as $event) {
            if (isset($data['title'])) {
                $event->setTitle($data['title']);
            }

            if (isset($data['description'])) {
                $event->setDescription($data['description']);
            }

            if (isset($data['category'])) {
                $event->setCategory($data['category']);
            }

            if (isset($data['public_event'])) {
                $event->setPublicEvent($data['public_event']);
            }
            if (isset($data['city'])) {
                $event->setCity($data['city']);
            }
            if (isset($data['school_id'])) {
                $event->setSchoolId($data['school_id']);
            }
            if (isset($data['start_date'])) {
                $event->setStartDate($data['start_date']);
            }
            if (isset($data['end_date'])) {
                $event->setEndDate($data['end_date']);
            }
            if (isset($data['image_url'])) {
                $event->setImageUrl($data['image_url']);
            }
            $dm->flush();
        }


        return new JsonResponse([
            'success' => true,
            'message' => 'Toute les event ont eté mise à jour avec succès.',
        ], Response::HTTP_OK);
    }

}