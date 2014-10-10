<?php

/**
 * @file
 * Base class for all email implementations.
 */

namespace Tudu\Email;

use Tudu\Email\EmailInterface;

class BaseEmail implements EmailInterface
{
    protected $body;
    protected $subject;
    protected $to;
    protected $from;
    protected $messageID;
    protected $recipients;

    /**
     * {@inheritDoc}
     */
    public function getTo()
    {
        return $this->to;
    }

    /**
     * {@inheritDoc}
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * {@inheritDoc}
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * {@inheritDoc}
     */
    public function getFrom()
    {
        return $this->from['raw'];
    }

    /**
     * {@inheritDoc}
     */
    public function getFromAddress()
    {
        return $this->from['address'];
    }

    /**
     * {@inheritDoc}
     */
    public function getFromName()
    {
        return $this->from['name'];
    }

    /**
     * {@inheritDoc}
     */
    public function getMessageID()
    {
        return $this->messageID;
    }

    /**
     * {@inheritDoc}
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * Normalize a plain body.
     *
     * Normalize new lines and carriage returns. Use only UNIX line feeds.
     *
     * @param string $body
     *   The email body, not containing any HTML.
     *
     * @return string
     *   The normalized email body.
     */
    protected function normalizePlainBody($body)
    {
        return str_replace(array("\r\n", "\r"), "\n", trim($body));
    }

    /**
     * Normalize an HTML body.
     *
     * This method removes all HTML tags and replaces <p>, <div> and <br> tags
     * with consistent new lines.
     *
     * @param string $body
     *   The email body, containing HTML.
     *
     * @return string
     *   The normalized email body.
     */
    protected function normalizeHTMLBody($body)
    {
        // Normalize new lines.
        $body = str_replace(array("\r\n", "\r"), "\n", $body);

        // Add new lines after all closing divs or paragraphs, to simulate
        // plain new lines.
        $body = preg_replace('/<\/div\s*>\n*/i', "</div>\n", $body);
        $body = preg_replace('/<\/p\s*>\n*/i', "</p>\n\n", $body);

        // Replace brs with newlines.
        $body = preg_replace('/<br\s*\/?>\n*/i', "\n", $body);

        // Remove all tags.
        $body = trim(strip_tags($body));

        // We assume we never have more than 2 subsequent new lines.
        // Markdown wouldn't parse it like that anyway.
        $body = preg_replace('/\n{3,}/', "\n\n", $body);

        return $body;
    }

    /**
     * Extract the name information from an RFC email recipient string.
     *
     * Extract the name of a recipient from a string like
     * "John Doe <john.doe@domain.com>".
     *
     * @param string $recipient
     *   The raw recipient string.
     *
     * @return string
     *   The extracted name, or an empty string if none was found.
     */
    protected function extractRecipientName($recipient)
    {
        $match = array();
        preg_match('/(.+)\s*</', $recipient, $match);
        return !empty($match[1]) ? trim($match[1], ' "\'') : '';
    }

    /**
     * Extract the address information from an RFC email recipient string.
     *
     * Extract the address of a recipient from a string like
     * "John Doe <john.doe@domain.com>".
     *
     * @param string $recipient
     *   The raw recipient string.
     *
     * @return string
     *   The extracted address.
     */
    protected function extractRecipientAddress($recipient)
    {
        $match = array();
        preg_match('/(\s|.<|^)([\w._%+-]+@[\w.-]+\.\w{2,4})>?$/i', $recipient, $match);
        return !empty($match[2]) ? $match[2] : '';
    }
}
