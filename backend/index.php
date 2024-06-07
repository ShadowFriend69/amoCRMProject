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

// Получаем последний результат из redis
$app->get('/redis/{url}', function ($request, $response, $args) use ($redis) {
    $url = $args['url'];
    $key = 'last_check_' . $url;
    $result = $redis->get($key);

    if ($result) {
        $result = json_decode($result, true);
        return $response->withJson($result);
    } else {
        return $response->withJson(['error' => 'No data found'], 404);
    }
});

// Получаем последний результат из elasticsearch
$app->get('/elasticsearch/{url}', function ($request, $response, $args) use ($es) {
    $url = $args['url'];
    $params = [
        'index' => 'website_checks',
        'body' => [
            'query' => [
                'match' => [
                    'url' => $url
                ]
            ],
            'sort' => [
                'timestamp' => ['order' => 'desc']
            ],
            'size' => 1
        ]
    ];

    $result = $es->search($params);
    if (isset($result['hits']['hits'][0])) {
        return $response->withJson($result['hits']['hits'][0]['_source']);
    } else {
        return $response->withJson(['error' => 'No data found'], 404);
    }
});

$app->run();
