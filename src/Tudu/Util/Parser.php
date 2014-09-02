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
     * @return array
     *   An array with 2 elements. The first element is one of Parser::DONE,
     *   Parser::ADD, Parser::DELETE, Parser::RESET or Parser::COMMENT.
     *   The second one is the parameter for the action.
     */
    public static function extractAction($body)
    {
        $body = trim(str_replace(array("\r\n", "\r"), "\n", $body));
        $match = array();

        if (preg_match('/^add\s*:\n*(.+)/i', $body, $match)) {
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
}
