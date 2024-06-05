<?php
require 'vendor/autoload.php';

use Predis\Client as RedisClient;
use Elasticsearch\ClientBuilder;
use Slim\App;

$redis = new RedisClient();
$es = ClientBuilder::create()->build();

function checkWebsite($url) {
    $start = microtime(true);
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $time = microtime(true) - $start;
    curl_close($ch);

    return [
        'url' => $url,
        'http_code' => $http_code,
        'response_time' => $time
    ];
}

$app = new App;

$app->get('/check', function ($request, $response, $args) use ($redis, $es) {
    $url = $request->getQueryParams()['url'];
    $result = checkWebsite($url);
    $redis->set('last_check_' . $url, json_encode($result));
    $es->index([
        'index' => 'website_checks',
        'body' => $result
    ]);

    return $response->withJson($result);
});

$app->run();
