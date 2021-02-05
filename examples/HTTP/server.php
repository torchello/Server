<?php

include __DIR__ . '../../../vendor/autoload.php';

use Rubix\ML\Datasets\Generators\Blob;
use Rubix\ML\Datasets\Generators\Agglomerate;
use Rubix\ML\Classifiers\KDNeighbors;
use Rubix\Server\HTTPServer;
use Rubix\Server\HTTP\Middleware\Server\AccessLogGenerator;
use Rubix\Server\HTTP\Middleware\Server\BasicAuthenticator;
use Rubix\Server\Services\Caches\InMemoryCache;
use Rubix\Server\Loggers\File;

$generator = new Agglomerate([
    'red' => new Blob([255, 0, 0], 10.0),
    'green' => new Blob([0, 128, 0], 10.0),
    'blue' => new Blob([0, 0, 255], 10.0),
]);

$estimator = new KDNeighbors(5);

$dataset = $generator->generate(100);

$estimator->train($dataset);

$logger = new File('server.log');

$server = new HTTPServer('127.0.0.1', 8000, null, [
    new AccessLogGenerator($logger),
    new BasicAuthenticator([
        'user' => 'secret',
    ]),
], 5, new InMemoryCache(0));

$server->setLogger($logger);

$server->serve($estimator);
