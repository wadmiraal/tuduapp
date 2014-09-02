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

    /**
     * Get the email subject, if any.
     *
     * Return the subject of the email, or an empty string if it's not available.
     *
     * @return string
     *   The subject of the email, or an empty string.
     */
    public function getSubject();

    /**
     * Get the email message ID, if any.
     *
     * Return the subject of the email, or an empty string if it's not available.
     *
     * @return string
     *   The subject of the email, or an empty string.
     */
    public function getMessageID();

    /**
     * Get the email CC recipients, if any.
     *
     * @return array
     *   The list of recipients. Each item has 2 keys:
     *   - address: The recipient email address
     *   - name: The recipient name, if any.
     */
    public function getRecipients();
}
