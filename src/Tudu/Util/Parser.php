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
     * Extract the action from the email body.
     *
     * @param string $body
     *   The email body.
     *
     * @return Parser::DONE|Parser::ADD|Parser::DELETE|Parser::RESET|Parser::COMMENT
     *   The action to take on the list. Defaults to Parser::COMMENT.
     */
    public static function extractAction($body)
    {
        $body = trim($body);

        if (preg_match('/^add\s*:/i', $body)) {
            return self::ADD;
        } else if (preg_match('/^delete\s*[0-9]+/i', $body)) {
            return self::DELETE;
        } else if (preg_match('/^done\s*[0-9]+/i', $body)) {
            return self::DONE;
        } else if (preg_match('/^reset\s*[0-9]+/i', $body)) {
            return self::RESET;
        } else {
            return self::COMMENT;
        }
    }
}
