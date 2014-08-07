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

    /**
     * Get the sender's "From" header.
     *
     * @return string
     *   The email "From" header.
     */
    public function getFrom();

    /**
     * Get the sender's email address.
     *
     * Return only the address of the sender, removing name and other
     * information if present.
     *
     * @return string
     *   The email address from the sender.
     */
    public function getFromAddress();

    /**
     * Get the sender's name, if any.
     *
     * Return only the name of the sender, removing address and other
     * information if present.
     *
     * @return string
     *   The name of the sender, or an empty string.
     */
    public function getFromName();

}
