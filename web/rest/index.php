<?php

/**
 * @file
 * Application end point.
 */

require_once __DIR__ . '/../../vendor/autoload.php';

$app = new Silex\Application();

require_once __DIR__ . '/../../app/config.php';
require_once __DIR__ . '/../../app/app.php';
require_once __DIR__ . '/../../app/routes.php';

$app['debug'] = true;

$app->run();
