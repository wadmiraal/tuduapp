<?php

/**
 * @file
 * Application service providers and set up.
 */

use Tudu\Model\Todo;

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => $conf['db.options'],
));

$app['todo_db_service'] = function($app) {
    return new Todo($app['db']);
};

$app['conf'] = function() {
    global $conf;
    return $conf;
};
