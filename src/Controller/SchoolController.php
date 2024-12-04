<?php

namespace App\Controller;

use App\Document\Address;
use App\Document\Geo;
use App\Document\Location;
use App\Document\School;
use Doctrine\ODM\MongoDB\DocumentManager;
use Doctrine\ODM\MongoDB\MongoDBException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class SchoolController extends AbstractController
{

    /**
     * @throws MongoDBException
     * @throws Throwable
     */
    #[Route('/schools', name: 'app_schools_add', methods: ['POST'])]
    public function addSchools(Request $request, DocumentManager $dm): JsonResponse
    {

        $data = json_decode($request->getContent(), true);

        $address = new Address();
        $address->setStreet($data['location']['address']['street']);
        $address->setCity($data['location']['address']['city']);
        $address->setZipcode($data['location']['address']['zipcode']);

        $geo = new Geo();
        $geo->setType($data['location']['geo']['type']);
        $geo->setCoordinates($data['location']['geo']['coordinates']);

        $location = new Location();
        $location->setAddress($address);
        $location->setGeo($geo);

        $schools = new School();
        $schools->setName($data['name']);
        $schools->setLocation($location);


        // Persister et enregistrer dans MongoDB
        $dm->persist($schools);
        $dm->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Schools ajouté avec succès.',
            'title' => $schools->getName(),
        ],Response::HTTP_OK);
    }

    #[Route('/schools/{_id}', name: 'app_schools_find', methods: ['GET'])]
    public function findSchools(Request $request, DocumentManager $dm, string $_id): JsonResponse
    {
        $schools = $dm->find(School::class, $_id);

        if (!$schools) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Schools introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }


        $schoolJson = [
            "id" => $schools->getId(),
            "name" => $schools->getName(),
            "location" => [
                "address" => [
                    "street" => $schools->getLocation()->getAddress()->getStreet(),
                    "city" => $schools->getLocation()->getAddress()->getCity(),
                    "zipcode" => $schools->getLocation()->getAddress()->getZipcode()
                ],
                "geo" => [
                    "type" => $schools->getLocation()->getGeo()->getType(),
                    "coordinates" => $schools->getLocation()->getGeo()->getCoordinates()
                ]
            ]
        ];



        return new JsonResponse([
            'success' => true,
            'message' => 'Scholl trouvé avec succès.',
            'school' => $schoolJson
        ],Response::HTTP_OK);

    }


    /**
     * @throws Throwable
     * @throws MongoDBException
     */
    #[Route('/schools/{_id}', name: 'app_schools_delete', methods: ['DELETE'])]
    public function deleteSchools(Request $request, DocumentManager $dm, string $_id): JsonResponse
    {
        $schools = $dm->find(School::class, $_id);

        if (!$schools) {
            return new JsonResponse([
                'success' => false,
                'message' => 'School introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }

        $dm->remove($schools);
        $dm->flush();


        return new JsonResponse([
            'success' => true,
            'message' => 'School supprimé avec succès.',
        ],Response::HTTP_OK);

    }

    #[Route('/schools', name: 'app_schools_find_all', methods: ['GET'])]
    public function findAllSchools(Request $request, DocumentManager $dm): JsonResponse
    {
        $schools = $dm->getRepository(School::class)->findAll();

        if (!$schools) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Schools introuvable.'
            ], Response::HTTP_NOT_FOUND);
        }

        $schoolsJson = array_map(function ($school) {
            return [
                "id" => $school->getId(),
                "name" => $school->getName(),
                "location" => [
                    "address" => [
                        "street" => $school->getLocation()->getAddress()->getStreet(),
                        "city" => $school->getLocation()->getAddress()->getCity(),
                        "zipcode" => $school->getLocation()->getAddress()->getZipcode()
                    ],
                    "geo" => [
                        "type" => $school->getLocation()->getGeo()->getType(),
                        "coordinates" => $school->getLocation()->getGeo()->getCoordinates()
                    ]
                ]
            ];
        }, $schools);

        return new JsonResponse([
            'success' => true,
            'message' => 'Events trouvé avec succès.',
            'schools' => $schoolsJson,
            'count' => count($schoolsJson)
        ],Response::HTTP_OK);

    }


    /**
     * @throws Throwable
     * @throws MongoDBException
     */
    #[Route('/schools/{_id}', name: 'app_schools_update', methods: ['PATCH'])]
    public function updateSchools(Request $request, DocumentManager $dm, string $_id): JsonResponse
    {

        $schools = $dm->find(School::class, $_id);

        if (!$schools) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Schools introuvable.'
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
            $schools->setName($data['name']);
        }

        if (isset($data['location'])) {
            $location = $schools->getLocation();

            if (!$location) {
                $location = new Location();
                $schools->setLocation($location);
            }

            if (isset($data['location']['address'])) {
                $address = $location->getAddress();

                if (!$address) {
                    $address = new Address();
                    $location->setAddress($address);
                }

                if (isset($data['location']['address']['street'])) {
                    $address->setStreet($data['location']['address']['street']);
                }
                if (isset($data['location']['address']['city'])) {
                    $address->setCity($data['location']['address']['city']);
                }
                if (isset($data['location']['address']['zipcode'])) {
                    $address->setZipcode($data['location']['address']['zipcode']);
                }
            }

            if (isset($data['location']['geo'])) {
                $geo = $location->getGeo();

                if (!$geo) {
                    $geo = new Geo();
                    $location->setGeo($geo);
                }

                if (isset($data['location']['geo']['type'])) {
                    $geo->setType($data['location']['geo']['type']);
                }
                if (isset($data['location']['geo']['coordinates'])) {
                    $geo->setCoordinates($data['location']['geo']['coordinates']);
                }
            }
        }


        $dm->flush();

        $schoolJson = [
            "id" => $schools->getId(),
            "name" => $schools->getName(),
            "location" => [
                "address" => [
                    "street" => $schools->getLocation()->getAddress()->getStreet(),
                    "city" => $schools->getLocation()->getAddress()->getCity(),
                    "zipcode" => $schools->getLocation()->getAddress()->getZipcode()
                ],
                "geo" => [
                    "type" => $schools->getLocation()->getGeo()->getType(),
                    "coordinates" => $schools->getLocation()->getGeo()->getCoordinates()
                ]
            ]
        ];

        return new JsonResponse([
            'success' => true,
            'message' => 'Event mise à jour avec succès.',
            'event' => $schoolJson
        ], Response::HTTP_OK);
    }


}