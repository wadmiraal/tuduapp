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
    public function inboxAction($service, $securityKey, Application $app, Request $request)
    {
        $conf = $app['conf'];

        if (!in_array($securityKey, $conf['security.keys'])) {
            return new Response("Access denied", 403);
        }

        switch($service) {
            case 'cloudmailin':
                $email = new CloudMailinEmail($request);
                break;
            case 'mailgun':
                $email = new CloudMailinEmail($request);
                break;
        }

        switch ($email->getTo()) {
            case $conf['tudu.emails.create']['address']:
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

                Notifier::notify($todo, $app);

                return new Response('Todo list created, [id:' . $todo->getID() . ']', 201);
                break;

            case $conf['tudu.emails.update']['address']:
                $todoID = Parser::extractTodoID($email->getSubject());

                if (!empty($todoID)) {
                    $todo = $app['todo_db_service'];
                    $todo->load($todoID);
                }

                if (empty($todo)) {
                    // @todo Notify sender the email was not usable.
                    return new Response("Unkown list ID", 404);
                }

                // Update the sender message ID.
                $todo->addParticipant($email->getFromAddress(), $email->getFromName(), $email->getMessageID());

                // If any new persons were Cc'ed, add them as well.
                $recipients = $email->getRecipients();

                if (!empty($recipients)) {
                    foreach ($recipients as $recipient) {
                        $todo->addParticipant($recipient['address'], $recipient['name'], $email->getMessageID());
                    }
                }

                // Extract the action and parameter.
                list($action, $parameter) = Parser::extractAction($email->getBody());

                switch ($action) {
                    case Parser::ADD:
                        list($metaDue, $metaAssignedTo) = Parser::extractTaskMeta($parameter, $todo);
                        $todo->addTask($parameter, $metaDue, $metaAssignedTo);
                        break;

                    case Parser::DELETE:
                        try {
                            $todo->removeTask($parameter);
                        } catch (\Exception $e) {}
                        break;

                    case Parser::DONE:
                        try {
                            $todo->setTaskState($parameter, true);
                        } catch (\Exception $e) {}
                        break;

                    case Parser::RESET:
                        try {
                            $todo->setTaskState($parameter, false);
                        } catch (\Exception $e) {}
                        break;

                    default:
                    case Parser::COMMENT:
                        // @todo
                        break;
                }

                $todo->save();

                Notifier::notify($todo, $app);

                return new Response('Todo list updated, [id:' . $todo->getID() . ']', 200);
                break;

            default:
                $to = $email->getTo();
                return new Response("Incorrect 'To' address. Received: {$to}", 400);
        }
    }
}
