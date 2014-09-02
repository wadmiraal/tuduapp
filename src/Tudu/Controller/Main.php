<?php

/**
 * @file
 * Main application controller.
 */

namespace Tudu\Controller;

use Tudu\Email\CloudMailinEmail;
use Tudu\Util\Notifier;
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
        $email = new CloudMailinEmail($request);

        switch ($email->getTo()) {
            case $conf['tudu.emails.create']:
                $todo = $app['todo_db_service'];

                $todo->setOwner($email->getFromAddress());
                $todo->setTitle($email->getSubject());
                $todo->addParticipant($email->getFromAddress(), $email->getFromName(), $email->getMessageID());

                $recipients = $email->getRecipients();

                if (!empty($recipients)) {
                    foreach ($recipients as $recipient) {
                        $todo->addParticipant($recipient['address'], $recipient['name'], $email->getMessageID());
                    }
                }

                $todo->save();

                Notifier::notify($todo);
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

                list($action, $parameter) = Parser::extractAction($email->getBody());



                // @todo:
                // - update the list as needed (if needed)
                // - load the email sender, and update her participant entry
                //   (because of the message ID)
                // - notify all participants
                break;

            default:
                // @todo Notify sender the email was not usable.
                $to = $email->getTo();
                return new Response("Incorrect 'To' address. Received: {$to}", 400);
        }

        return new Response('OK', 201);
    }
}
