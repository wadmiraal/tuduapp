<?php

/**
 * @file
 * Route definitions.
 */

use Tudu\Email\CloudMailinEmail;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app->post('/inbox', function(Application $app, Request $request) {
    $email = new CloudMailinEmail($request);

    switch ($email->getTo()) {
        case $conf['tudu.emails.create']:
            // @todo Put this inside a class of some sorts.
            $todo = $app['todo_db_service'];
            $todo->setOwner($email->getFromAddress());
            $todo->setTitle($email->getSubject());
            $todo->addParticipant($email->getFromAddress(), $email->getFromName(), $email->getMessageID());

            // @todo Use CC for other participants.

            $todo->save();

            // @todo Notify participants.
            break;

        case $conf['tudu.emails.update']:

            break;

        default:
            $to = $email->getTo();
            return new Response("Incorrect 'To' address. Received: {$to}", 400);
    }

    return new Response('OK', 201);
});
