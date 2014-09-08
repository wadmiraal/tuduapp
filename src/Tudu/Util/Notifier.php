<?php

/**
 * @file
 * Notifies list participants via email of list updates or
 * creation.
 */

namespace Tudu\Util;

use Michelf\Markdown;

class Notifier
{
    /**
     * Notify all participants of the list of changes or creation.
     *
     * Each email is tailored to the recipient. When the recipient name appears
     * for others, it will show "you" for the recipient itself, for example.
     *
     * @param Tudu\Model\Todo $todo
     *   The todo list model.
     * @param \Silex\Application $app
     *   The application.
     */
    public static function notify(\Tudu\Model\Todo $todo, \Silex\Application $app)
    {
        $tasks = array();
        $doneTasks = array();
        $conf = $app['conf'];

        foreach ($todo->getTasks() as $task) {
            $taskDescription = preg_replace('/\[.*(assigned to|due)\s*:\s*([^\]]+)\]/', '', $task->getTask());
            $taskDescription = Markdown::defaultTransform($taskDescription);
            $taskDescription = strip_tags($taskDescription, '<a><em><i><b><strong><code>');

            if ($task->getDone()) {
                $doneTasks[$task->getNum()] = $taskDescription;
            } else {
                $tasks[$task->getNum()] = $taskDescription;
            }
        }

        $body = $todo->getDescription();

        $body .= "\n\n";

        foreach ($tasks as $num => $task) {
            $body .= "[$num] $task\n";
        }

        foreach ($doneTasks as $num => $task) {
            $body .= "--[$num] $task--\n";
        }

        $from = $conf['tudu.emails.update'];
        if (!empty($conf['tudu.emails.names'])) {
            $name = $conf['tudu.emails.names'][array_rand($conf['tudu.emails.names'])];
            $from = "\"$name\" <$from>"; 
        }

        if (!empty($conf['tudu.emails.signatures.html'])) {
            $body .= "\n\n" . $conf['tudu.emails.signatures.html'][array_rand($conf['tudu.emails.signatures.html'])];
        }

        // Skip sending email if we're in the development environment.
        if (!(defined('APP_ENV') && APP_ENV === 'development')) {
            foreach ($todo->getParticipants() as $participant) {
                mail(
                    $participant->getEmail(),
                    $todo->getTitle() . ' [id:' . $todo->getID() . ']',
                    $body,
                    implode("\r\n", array(
                        'From: ' . $from,
                        'In-Reply-To: ' . $participant->getLastMessageID(),
                    ))
                );   
            }
        }

        return $body;
    }
}
