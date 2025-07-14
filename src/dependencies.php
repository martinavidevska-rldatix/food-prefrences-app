<?php

use DI\Container;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\DBAL\DriverManager;
use Doctrine\ORM\ORMSetup;
use src\services\PersonService;
use src\services\FruitService;
use src\repository\PersonRepository;
use src\repository\FruitRepository;
use src\controllers\PersonController;
use src\controllers\FruitController;
use Predis\Client as RedisClient;
use src\configuration\RedisConfiguration;
use src\cache\IPersonCache;
use src\cache\RedisPersonCache;

return function (Container $container) {
    // === Doctrine EntityManager ===
    $container->set(EntityManagerInterface::class, function () {
        $config = ORMSetup::createAttributeMetadataConfiguration(
            [__DIR__ . '/../src/models'], // directory containing your entity classes
            true, // dev mode
        );

        $conn =  DriverManager::getConnection([
            'dbname'   => 'mydatabase',
            'user'     => 'user',
            'password' => 'secret',
            'host'     => 'db', 
            'driver'   => 'pdo_mysql',
        ]);

        return new EntityManager($conn, $config);
    });

    // === Repositories ===
    $container->set(PersonRepository::class, function ($c) {
        return $c->get(EntityManagerInterface::class)
                 ->getRepository(\src\models\Person::class);
    });

    $container->set(FruitRepository::class, function ($c) {
        return $c->get(EntityManagerInterface::class)
                 ->getRepository(\src\models\Fruit::class);
    });

    // === Services ===
    $container->set(PersonService::class, function ($c) {
        return new PersonService(
            $c->get(EntityManagerInterface::class),
            $c->get(IPersonCache::class)
        );
    });

    $container->set(FruitService::class, function ($c) {
        return new FruitService($c->get(EntityManagerInterface::class));
    });

    // === Controllers ===
    $container->set(PersonController::class, function ($c) {
        return new PersonController(
            $c->get(PersonService::class),
            $c->get(FruitService::class)
        );
    });

    $container->set(FruitController::class, function ($c) {
        return new FruitController($c->get(FruitService::class));
    });

    // === Redis (Optional) ===
    $container->set(RedisConfiguration::class, function () {
        return new RedisConfiguration(
            host: 'redis',     
            port: 6379,
        );
    });
 
    $container->set(RedisClient::class, function ($c) {
        $config = $c->get(RedisConfiguration::class);
        return new RedisClient([
            'scheme' => 'tcp',
            'host' => $config->databaseHost,
            'port' => $config->databasePort,
        ]);
    });

    $container->set(IPersonCache::class, function ($c) {
        return new RedisPersonCache($c->get(RedisClient::class));
    });
};
