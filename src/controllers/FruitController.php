<?php

namespace src\controllers;

use src\services\FruitService;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class FruitController
{
    public function __construct(private FruitService $fruitService) {}

    public function list(Request $request, Response $response): Response
    {
        $fruits = $this->fruitService->getAllFruits();
        return $this->jsonResponse($response, $fruits);
    }

    public function create(Request $request, Response $response): Response
    {
       $data = $request->getParsedBody();
        if ($data==null) {
             $raw = (string) $request->getBody();
              error_log("RAW: " . $raw);
            $data = json_decode($raw, true);
        }
        echo $data['name'];
    

        $fruit = $this->fruitService->createFruit($data['name']);

        $payload = [
            'id' => $fruit->getId(),
            'name' => $fruit->getName(),
        ];

        return $this->jsonResponse($response, $payload, 201);
    }

    private function jsonResponse(Response $response, mixed $data, int $status = 200): Response
    {
        $payload = json_encode($data, JSON_UNESCAPED_UNICODE);
        $response->getBody()->write($payload);
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus($status);
    }
}
