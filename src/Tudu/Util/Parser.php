<?php

/**
 * @file
 * Parses the email to extract relevant data.
 */

namespace Tudu\Util;

class Parser
{
    const DONE = 'done';
    const ADD = 'add';
    const DELETE = 'delete';
    const RESET = 'reset';
    const COMMENT = 'comment';

    /**
     * Extract the list ID from the email subject.
     *
     * @param string $subject
     *   The email subject.
     *
     * @return string|null
     *   The list ID, or null otherwise.
     */
    public static function extractTodoID($subject)
    {
        $subject = trim($subject);
        $match = array();
        if (preg_match('/\[id:[\w\.]+\]/i', $subject, $match)) {
            return str_replace('[id:', '', str_replace(']', '', $match[0]));
        }
    }

    /**
     * Extract the introduction text and tasks from the email body.
     *
     * Parse the email body and extract the first paragraphs - which will serve
     * as the list description - and parse all listed tasks, returning them in
     * an array.
     * A list of tasks can either be a list with '-' or '*' bullets.
     *
     * @param string $body
     *   The email body.
     *
     * @return array
     *   An array with 2 elements. The first element is the list description (or
     *   an empty string) and the second a list of tasks.
     */
    public static function extractTodoList($body)
    {
        $body = trim(str_replace(array("\r\n", "\r"), "\n", $body));

        // Get the description. Split on the first task.
        $parts = preg_split('/(\n?\s*-|(\n+|^)\s*\*\s+)/', $body);
        $description = trim($parts[0]);

        // Get the tasks.
        $matches = array();
        $tasks = array();

        if (preg_match_all('/(\n?\s*-|(\n+|^)\s*\*\s+)\s*(.+)/', $body, $matches)) {
            if (!empty($matches[3])) {
                $tasks = $matches[3];
            }
        }

        return array($description, $tasks);
    }

    /**
     * Extract the action from the email body.
     *
     * @param string $body
     *   The email body.
     *
     * @return array
     *   An array with 2 elements. The first element is one of Parser::DONE,
     *   Parser::ADD, Parser::DELETE, Parser::RESET or Parser::COMMENT.
     *   The second one is the parameter for the action.
     */
    public static function extractAction($body)
    {
        $body = trim(str_replace(array("\r\n", "\r"), "\n", $body));
        $match = array();

        if (preg_match('/^add[\s\n]*(.+)/i', $body, $match)) {
            return array(self::ADD, trim($match[1]));
        } else if (preg_match('/^delete\s*([0-9]+)/i', $body, $match)) {
            return array(self::DELETE, (int) $match[1]);
        } else if (preg_match('/^done\s*([0-9]+)+/i', $body, $match)) {
            return array(self::DONE, (int) $match[1]);
        } else if (preg_match('/^reset\s*([0-9]+)+/i', $body, $match)) {
            return array(self::RESET, (int) $match[1]);
        } else {
            $parts = explode("\n\n", $body);
            return array(self::COMMENT, $parts[0]);
        }
    }

    /**
     * Extract a task meta data.
     *
     * Extract a task meta data, due date and assigned person. If the assigned
     * person is not an email, check if the person is a participant of the todo
     * list.
     *
     * @param string $task
     *   The task string.
     * @param \Tudu\Model\Todo $todo = null
     *   An optional todo list to check participants.
     *
     * @return array
     *   The first key is the meta due date, the second is the assigned person.
     */
    public static function extractTaskMeta($task, $todo = null)
    {
        $due = '';
        $match = array();
        if (preg_match('/\[.*due\s*:\s*([^\]]+)\]/', $task, $match)) {
            $match[1] = trim($match[1]);

            $time = strtotime($match[1]);

            if ($time) {
                $due = date('Y-m-d H:i:s', $time);
            } else {
                $due = $match[1];
            }
        }

        $assignedTo = '';
        $match = array();
        if (preg_match('/\[.*assigned to\s*:\s*([^\]]+)\]/', $task, $match)) {
            $assignedTo = trim($match[1]);

            // If not an email address, try checking against the todo
            // participant list.
            if (isset($todo) && !preg_match('/[\w._%+-]+@[\w.-]+\.\w{2,4}/', $assignedTo)) {
                foreach ($todo->getParticipants() as $participant) {
                    // Cast both strings to lowercase to compare.
                    $tmpAssignedTo = strtolower($assignedTo);
                    $tmpName = strtolower($participant->getName());

                    // Compare the first part of the email with the name.
                    $perc = 0;
                    similar_text($tmpAssignedTo, @reset(explode('@', $participant->getEmail())), $perc);
                    if ($tmpAssignedTo === $tmpName || $perc > 75) {
                        $assignedTo = $participant->getEmail();
                        break;
                    }
                }
            }
        }

        return array($due, $assignedTo);
    }
}
