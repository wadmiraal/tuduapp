<?php

/**
 * @file
 * Main application controller.
 */

namespace Tudu\Controller;

use Tudu\Email\CloudMailinEmail;
use Tudu\Util\Notifier;
use Tudu\Util\Parser;
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Main
{
    /**
     * Email "inbox" method.
     *
     * Called whenever we receive a new email. Parses the email, extracts the
     * information via the correct Tudu\Email class and persists the data.
     *
     * Notifies the participants of the changes/creation via the
     * Tudu\Util\Notifier class.
     */
    public function inboxAction(Application $app, Request $request)
    {
        $conf = $app['conf'];

        $email = new CloudMailinEmail($request);

        switch ($email->getTo()) {
            case $conf['tudu.emails.create']:
                $todo = $app['todo_db_service'];

                list($description, $tasks) = Parser::extractTodoList($email->getBody());

                $todo->setTitle($email->getSubject());
                $todo->setDescription($description);
                $todo->setOwner($email->getFromAddress());
                $todo->addParticipant($email->getFromAddress(), $email->getFromName(), $email->getMessageID());

                $recipients = $email->getRecipients();

                if (!empty($recipients)) {
                    foreach ($recipients as $recipient) {
                        $todo->addParticipant($recipient['address'], $recipient['name'], $email->getMessageID());
                    }
                }

                if (!empty($tasks)) {
                    foreach ($tasks as $task) {
                        $todo->addTask($task);
                    }
                }

                $todo->save();

                Notifier::notify($todo);

                return new Response('Todo list created, [id:' . $todo->getID() . ']', 201);
                break;

            case $conf['tudu.emails.update']:
                $todoID = Parser::extractTodoID($email->getSubject());

                if (!empty($todoID)) {
                    $todo = $app['todo_db_service'];
                    $todo->load($todoID);
                }

                if (empty($todo)) {
                    // @todo Notify sender the email was not usable.
                    return new Response("Incorrect 'To' address. Received: {$to}", 400);
                }

                // Update the sender message ID.
                $todo->addParticipant($email->getFromAddress(), $email->getFromName(), $email->getMessageID());

                // Extract the action and parameter.
                list($action, $parameter) = Parser::extractAction($email->getBody());

                switch ($action) {
                    case Parser::ADD:
                        // @todo
                        break;

                    case Parser::DELETE:
                        // @todo
                        break;

                    case Parser::RESET:
                        // @todo
                        break;

                    default:                        
                    case Parser::COMMENT:
                        // @todo
                        break;
                }

                Notifier::notify($todo);

                return new Response('Todo list with id ' . $todo->getID() . ' updated', 200);
                break;

            default:
                $to = $email->getTo();
                return new Response("Incorrect 'To' address. Received: {$to}", 400);
        }
    }
}
