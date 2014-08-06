<?php

/**
 * @file
 * Application service providers and set up.
 */

$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'db.options' => $conf['db.options'],
));
