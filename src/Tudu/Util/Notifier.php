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
     */
    public static function notify($todo)
    {
        // $my_html = Markdown::defaultTransform($my_text);

        // Skip sending email if we're in the development environment.
        if (!(defined('APP_ENV') && APP_ENV === 'development')) {
            mail();
        }
    }
}
