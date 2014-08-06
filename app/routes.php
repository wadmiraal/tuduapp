<?php

/**
 * @file
 * Route definitions.
 */

use Tudu\Email\CloudMailinEmail;
use Tudu\Utils\Notifier;
use Tudu\Utils\TodoCreator;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app->post('/inbox', function(Application $app, Request $request) {
    $email = new CloudMailinEmail($request);

    switch ($email->getTo()) {
        case $conf['tudu.emails.create']:
            break;

        case $conf['tudu.emails.update']:

            break;

        default:
            $to = $email->getTo();
            return new Response("Incorrect 'To' address. Received: {$to}", 500);
    }

    return new Response('OK', 201);
});
