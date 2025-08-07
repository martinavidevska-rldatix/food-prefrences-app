<?php

declare(strict_types=1);

use DI\Container;
use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\Stream;
use src\controllers\PersonController;
use src\controllers\FruitController;
use reports\ReportPublisher;

require __DIR__ . '/vendor/autoload.php';

$container = new Container();

(require __DIR__ . '/src/dependencies.php')($container);

AppFactory::setContainer($container);
$app = AppFactory::create();

$app->addBodyParsingMiddleware();
$app->addErrorMiddleware(true, true, true);

// === Routes ===

$app->get('/api/search', function (Request $request, Response $response) use ($container) {
    return $container->get(PersonController::class)->search($request, $response);
});

$app->get('/api/people', function (Request $request, Response $response) use ($container) {
    return $container->get(PersonController::class)->list($response);
});

$app->get('/api/people/{id}', function (Request $request, Response $response, array $args) use ($container) {
    $id = (int)$args['id'];
    return $container->get(PersonController::class)->getPersonByIdWithFruits($response, $id);
});

$app->post('/api/people', function (Request $request, Response $response) use ($container) {
    return $container->get(PersonController::class)->create($request, $response);
});

$app->put('/api/people/{id}', function (Request $request, Response $response, array $args) use ($container) {
    $id = (int)$args['id'];
    return $container->get(PersonController::class)->update($request, $response, $id);
});

$app->delete('/api/people/{id}', function (Request $request, Response $response, array $args) use ($container) {
    $id = (int)$args['id'];
    return $container->get(PersonController::class)->delete($request, $response, $id);
});

$app->post('/api/people/{id}/fruit', function (Request $request, Response $response, array $args) use ($container) {
    $id = (int)$args['id'];
    return $container->get(PersonController::class)->addFruit($request, $response, $id);
});

$app->get('/api/fruits', function (Request $request, Response $response) use ($container) {
    return $container->get(FruitController::class)->list($request, $response);
});

$app->post('/api/fruits', function (Request $request, Response $response) use ($container) {
    return $container->get(FruitController::class)->create($request, $response);
});

$app->post('/report/generate', function (Request $request, Response $response) {
    $reportId = uniqid('report_', true);

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

$app->get('/report/download/{id}', function (Request $request, Response $response, array $args) {
    $reportId = $args['id'];
    $filePath = __DIR__ . "/reports/generatedReports/{$reportId}.csv";

    if (!file_exists($filePath)) {
        $response->getBody()->write('Report not found.');
        return $response->withStatus(404);
    }

    $stream = new Stream(fopen($filePath, 'rb'));
    return $response
        ->withBody($stream)
        ->withHeader('Content-Type', 'text/csv')
        ->withHeader('Content-Disposition', "attachment; filename=\"{$reportId}.csv\"");
});
$app->get('/', function (Request $request, Response $response) {
    $html = file_get_contents(__DIR__ . '/start.php');
    $response->getBody()->write($html);
    return $response->withHeader('Content-Type', 'text/html');
});

$app->run();
