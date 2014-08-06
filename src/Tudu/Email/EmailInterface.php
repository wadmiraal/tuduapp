<?php

/**
 * @file
 * Defines the Email object interface.
 *
 * All email objects, or "parsers", must implement this interface. It describes
 * several methods which allow callers to retrieve data from the email, like the
 * sender, the date, the message-ID, etc.
 */

namespace Tudu\Email;

interface EmailInterface
{

    /**
     * Get the email body.
     *
     * Get the email body in plain text, without any formatting.
     *
     * @return string
     *   The email body, in plain text.
     */
    public function getBody();

}
