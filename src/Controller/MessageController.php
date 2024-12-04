<?php

namespace App\Controller;

use App\Document\Discussions;
use App\Document\Message;
use App\Document\User;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class MessageController extends AbstractController
{

    /**
     * @throws Throwable
     * @throws MongoDBException
     */
    #[Route('/messages', name: 'app_messages_add', methods: ['POST'])]
    public function addMessages(Request $request, DocumentManager $dm): JsonResponse
    {

        $data = json_decode($request->getContent(), true);

        $users = $dm->find(User::class, $data['user_id']);

        $discussions = $dm->find(Discussions::class, $data['discussion_id']);


        $messages = new Message();
        $messages->setDate($data['date']);
        $messages->setText($data['text']);
        $messages->setDiscussionId($discussions);
        $messages->setUserId($users);

        // Persister et enregistrer dans MongoDB
        $dm->persist($messages);
        $dm->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Message ajouté avec succès.',
            'title' => $discussions->getSubject(),
        ], Response::HTTP_OK);
    }

    #[Route('/messages/{id}', name: 'app_messages_find', methods: ['GET'])]
    public function findMessages(Request $request, DocumentManager $dm, string $id): JsonResponse
    {
        $messages = $dm->find(Message::class, $id);

        if (!$messages) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Messages introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }


        $messagesJson = [
            "id" => $messages->getId(),
            "user_id" => $messages->getUserId(),
            "discussion_id" => $messages->getDiscussionId(),
            "text" => $messages->getText(),
            "date" => $messages->getDate()
        ];


        return new JsonResponse([
            'success' => true,
            'message' => 'Messages trouvé avec succès.',
            'messages' => $messagesJson
        ], Response::HTTP_OK);

    }

    #[Route('/messages/lastMessage', name: 'app_messages_find', methods: ['GET'])]
    public function findLastMessages(Request $request, DocumentManager $dm): JsonResponse
    {
        $messages = $dm->getRepository(Message::class)->findBy(
            [],
            ['date' => 'DESC'],
            1
        );


        if (!$messages) {
            return new JsonResponse([
                'success' => false,
                'message' => ' Pas de message'
            ], Response::HTTP_NOT_FOUND);
        }

        $messagesJson = [
            "id" => $messages[0]->getId(),
            "user_id" => $messages[0]->getUserId(),
            "discussion_id" => $messages[0]->getDiscussionId(),
            "text" => $messages[0]->getText(),
            "date" => $messages[0]->getDate()
        ];


        return new JsonResponse([
            'success' => true,
            'message' => 'Messages trouvé avec succès.',
            'messages' => $messagesJson
        ], Response::HTTP_OK);

    }

    /**
     * @throws MongoDBException
     * @throws Throwable
     */
    #[Route('/messages/{id}', name: 'app_messages_delete', methods: ['DELETE'])]
    public function deleteMessages(Request $request, DocumentManager $dm, string $id): JsonResponse
    {
        $messages = $dm->find(Message::class, $id);

        if (!$messages) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Message introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }

        $dm->remove($messages);
        $dm->flush();


        return new JsonResponse([
            'success' => true,
            'message' => 'Message supprimé avec succès.',
        ], Response::HTTP_OK);

    }

    #[Route('/messages', name: 'app_messages_find_all', methods: ['GET'])]
    public function findAllDiscussions(Request $request, DocumentManager $dm): JsonResponse
    {
        $messages = $dm->getRepository(Message::class)->findAll();

        if (!$messages) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Message introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }

        $messagesJson = array_map(function ($message) {
            return [
                "id" => $message->getId(),
                "user_id" => $message->getUserId(),
                "discussion_id" => $message->getDiscussionId(),
                "text" => $message->getText(),
                "date" => $message->getDate()
            ];
        }, $messages);

        return new JsonResponse([
            'success' => true,
            'message' => 'Discussions trouvé avec succès.',
            'messages' => $messagesJson,
            'count' => count($messagesJson)
        ], Response::HTTP_OK);

    }

    /**
     * @throws Throwable
     * @throws MongoDBException
     */
    #[Route('/messages/{_id}', name: 'app_messages_update', methods: ['PATCH'])]
    public function updateMessages(Request $request, DocumentManager $dm, string $_id): JsonResponse
    {

        $messages = $dm->find(Message::class, $_id);

        if (!$messages) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Message introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (!$data) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Données invalides ou manquantes.'
            ], Response::HTTP_BAD_REQUEST);
        }

        if (isset($data['text'])) {
            $messages->setDescription($data['text']);
        }

        if (isset($data['date'])) {
            $messages->setDescription($data['date']);
        }

        $dm->flush();

        $messagesJson = [
            "id" => $messages->getId(),
            "user_id" => $messages->getUserId(),
            "discussion_id" => $messages->getDiscussionId(),
            "text" => $messages->getText(),
            "date" => $messages->getDate()
        ];

        return new JsonResponse([
            'success' => true,
            'message' => 'Discussions mise à jour avec succès.',
            'messages' => $messagesJson
        ], Response::HTTP_OK);
    }

}