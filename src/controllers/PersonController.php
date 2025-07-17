<?php

namespace src\controllers;

use src\services\PersonService;
use src\services\FruitService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use src\models\Person;

class PersonController
{
    public function __construct(
        private PersonService $personService,
        private FruitService $fruitService
    ) {}

    public function list(Request $request, Response $response): Response
    {
        $people = $this->personService->getAllPeople();
        return $this->jsonResponse($response, $people);
    }

    public function show(Request $request, Response $response, int $id): Response
    {
        $person = $this->personService->findPerson($id);
        if (!$person) {
            return $this->jsonResponse($response, ['error' => 'Person not found'], 404);
        }

        $preferredFruits = $this->personService->getPreferredFruits($id);
        $data = [
            'person' => $person,
            'preferred_fruits' => $preferredFruits
        ];

        return $this->jsonResponse($response, $data);
    }

    public function create(Request $request, Response $response): Response
    {
            $data = $request->getParsedBody();
        if ($data === null) {
            $raw = (string) $request->getBody();
            error_log("RAW: " . $raw);
            $data = json_decode($raw, true);
        }
        $newPerson = $this->personService->createPerson($data['firstName'], $data['lastName']);
        return $this->jsonResponse($response, $newPerson, 201);
    }
    
    public function update(Request $request, Response $response, int $id): Response
    {
        $data = $request->getParsedBody();

         if ($data === null) {
            $raw = (string) $request->getBody();
            error_log("RAW: " . $raw);
            $data = json_decode($raw, true);
        }

        $updatedPerson = new Person();
        $refl = new \ReflectionClass($updatedPerson);
        $refl->getProperty('firstName')->setValue($updatedPerson, $data['firstName']);
        $refl->getProperty('lastName')->setValue($updatedPerson, $data['lastName']);

        try {
            $result = $this->personService->updatePerson($id, $updatedPerson);
            return $this->jsonResponse($response, $result);
        } catch (\Exception $e) {
            return $this->jsonResponse($response, ['error' => $e->getMessage()], 404);
        }
    }

    public function delete(Request $request, Response $response, int $id): Response
    {
        try {
            $this->personService->deletePerson($id);
            return $this->jsonResponse($response, ['message' => 'Person deleted']);
        } catch (\Exception $e) {
            return $this->jsonResponse($response, ['error' => $e->getMessage()], 404);
        }
    }

    public function addFruit(Request $request, Response $response, int $personId): Response
    {
        $data = $request->getParsedBody();
        if ($data==null) {
             $raw = (string) $request->getBody();
              error_log("RAW: " . $raw);
            $data = json_decode($raw, true);
        }

        $this->personService->addPreferredFruit($personId, (int)$data['fruit_id']);
        return $this->jsonResponse($response, ['message' => 'Fruit added']);
    }

    private function jsonResponse(Response $response, mixed $data, int $status = 200): Response
    {
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE);
        $response->getBody()->write($payload);
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }

    public function listWithFruits(Request $request, Response $response): Response
    {
        $people = $this->personService->getAllPeopleWithFruits();
        return $this->jsonResponse($response, $people);
    }
   public function search(Request $request, Response $response): Response
{
    $queryParams = $request->getQueryParams();
    $firstName = $queryParams['param'] ?? '';

    if (empty($firstName)) {
        return $this->jsonResponse($response, ['error' => 'Missing firstName'], 400);
    }

    $people = $this->personService->searchByFirstName($firstName);

    return $this->jsonResponse($response, $people);
}




}
