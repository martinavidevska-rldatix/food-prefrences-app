<?php
require __DIR__ . '/vendor/autoload.php';

use Predis\Client;

$person_id = 21;
$requestCount = 1000;
$url = "http://localhost:8080/api/persons/{$person_id}";

function benchmark($url, $count){
    $start = microtime(true);
    for ($i = 0; $i < $count; $i++) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }

    $end = microtime(true);
    return $end - $start;
}
function benchmarkWithOutRedis($url, $count): float{
    $start = microtime(true);
    for ($i = 0; $i < $count; $i++) {
        clearRedisCache(21);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_exec($ch);
        curl_close($ch);
    }

    $end = microtime(true);
    return $end - $start;
}


function clearRedisCache($id): void
{
    $redis = new Client();
//    $redis->connect('localhost', 6379);
    $redis->del("person_{$id}");
}

$timeWithCache = benchmark($url, $requestCount);
echo "Time with cache: {$timeWithCache} seconds\n";

clearRedisCache($person_id);

$timeWithoutCache = benchmarkWithOutRedis($url, $requestCount);
echo "Time without cache: {$timeWithoutCache} seconds\n";

