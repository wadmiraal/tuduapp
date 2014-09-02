<?php

/**
 * @file
 * Notifies list participants via email of list updates or
 * creation.
 */

namespace Tudu\Util;

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

    }
}
