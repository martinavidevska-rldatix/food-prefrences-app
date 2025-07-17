<?php

declare(strict_types=1);

use DI\Container;
use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use src\controllers\PersonController;
use src\controllers\FruitController;
use reports\ReportPublisher;

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
//update a person
$app->put('/api/people/{id}', function (Request $request, Response $response, array $args) use ($container){
    $id = (int) $args['id'];
    return $container->get(PersonController::class)->update($request,$response, $id);
});

$app->delete('/api/people/{id}', function(Request $request, Response $response, array $args) use($container){
    $id = (int) $args['id'];
    return $container->get(PersonController::class)->delete($request,$response, $id);
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

$app->post('/report/generate', function (Request $request, Response $response) {
    $reportId =uniqid('report_', true);

    $publisher = new ReportPublisher();
    $publisher->publish($reportId);

    $data = json_encode(['report_id' => $reportId]);

    $response->getBody()->write($data);
    return $response->withHeader('Content-Type', 'application/json');
});
$app->post('/report/generate1', function (Request $request, Response $response) {
    $reportId =uniqid('report_', true);

    $publisher = new ReportPublisher();
    $publisher->publish($reportId);

    $data = json_encode(['report_id' => $reportId]);

    $response->getBody()->write($data);
    return $response->withHeader('Content-Type', 'application/json');
});


//Check report status
$app->get('/report/status', function (Request $request, Response $response) {
    $reportId = $request->getQueryParams()['report_id'] ?? '';
    $isReady = file_exists(__DIR__ . "/reports/generatedReports/{$reportId}.csv");

    $response->getBody()->write(json_encode(['ready' => $isReady]));
    return $response->withHeader('Content-Type', 'application/json');
});

// Download report
$app->get('/report/download/{id}', function (Request $request, Response $response, array $args) {
    $reportId = $args['id'];
    $filePath = __DIR__ . "/reports/generatedReports/{$reportId}.csv";

    if (!file_exists($filePath)) {
        $response->getBody()->write('Report not found.');
        return $response->withStatus(404);
    }

    $stream = new \Slim\Psr7\Stream(fopen($filePath, 'rb'));
    return $response
        ->withBody($stream)
        ->withHeader('Content-Type', 'text/csv')
        ->withHeader('Content-Disposition', "attachment; filename=\"{$reportId}.csv\"");
});

// // === Run App ===
$app->run();
