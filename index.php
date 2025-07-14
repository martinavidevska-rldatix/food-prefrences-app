<?php

declare(strict_types=1);

use DI\Container;
use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use src\controllers\PersonController;
use src\controllers\FruitController;

require __DIR__ . '/vendor/autoload.php';

// === Create Container ===
$container = new Container();

// === Register Dependencies ===
(require __DIR__ . '/src/dependencies.php')($container);

// === Create Slim App ===
AppFactory::setContainer($container);
$app = AppFactory::create();

// === Add Error Middleware (Dev Mode) ===
$app->addErrorMiddleware(true, true, true);

// === Routes ===

$app->get('/api/search', function (Request $request, Response $response) use ($container) {
    return $container->get(\src\controllers\PersonController::class)->search($request, $response);
});
// List all people
$app->get('/api/people', function (Request $request, Response $response) use ($container) {
     return $container->get(PersonController::class)->list($request, $response);
});

// Get one person and their preferred fruits
$app->get('/api/people/{id}', function (Request $request, Response $response, array $args) use ($container) {
    $id = (int) $args['id'];
    return $container->get(PersonController::class)->show($request, $response, $id);
});

// Create a new person
$app->post('/api/people', function (Request $request, Response $response) use ($container) {
    return $container->get(PersonController::class)->create($request, $response);
});

// Add a preferred fruit to a person
$app->post('/api/people/{id}/fruit', function (Request $request, Response $response, array $args) use ($container) {
    $id = (int) $args['id'];
    return $container->get(PersonController::class)->addFruit($request, $response, $id);
});

// Optional: List fruits
$app->get('/api/fruits', function (Request $request, Response $response) use ($container) {
    return $container->get(FruitController::class)->list($request, $response);
});

// Optional: Create fruit
$app->post('/api/fruits', function (Request $request, Response $response) use ($container) {
    return $container->get(FruitController::class)->create($request, $response);
});

// Get all people with their preferred fruits
$app->get('/api/people-fruits', function (Request $request, Response $response) use ($container) {
    return $container->get(PersonController::class)->listWithFruits($request, $response);
});



// // === Run App ===
$app->run();
