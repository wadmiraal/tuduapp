<?php

/**
 * @file
 * Cloud Mailin implementation for the email HTTP request.
 *
 * Whenever an email is received through Cloud Mailin, this class is invoked to
 * parse the request.
 */

namespace Tudu\Email;

use Tudu\Email\EmailInterface;
use Symfony\Component\HttpFoundation\Request;

class CloudMailinEmail implements EmailInterface
{

    const MULTIPART = 'multipart';
    const JSON = 'json';
    const RAW = 'raw';

    protected $body;
    protected $subject;
    protected $to;
    protected $from;
    protected $messageID;
    protected $recipients;

    /**
     * Parse the passed POST request.
     *
     * Use the Symfony\Component\HttpFoundation\Request object to parse the
     * request and extract all required information.
     * The way the data was given through POST can be set through the $mode
     * parameter.
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     *   The request object. Must be a POST (or PUT) request.
     * @param string $mode
     *   One of CloudMailinEmail::MULTIPART, CloudMailinEmail::JSON or
     *   CloudMailinEmail::RAW
     *
     * @throws Exception
     *   If the passed mode is not implemented or unknown, it throw an error.
     */
    public function __construct(Request $request, $mode = CloudMailinEmail::MULTIPART)
    {
        switch ($mode) {
            case CloudMailinEmail::MULTIPART:
                $this->body = $this->extractMultipartBody($request);
                $this->subject = $this->extractMultipartSubject($request);
                $this->from = $this->extractMultipartFrom($request);
                $this->messageID = $this->extractMultipartMessageID($request);
                $this->recipients = $this->extractMultipartRecipients($request);
                break;

            default:
                throw new \Exception("Mode is not available or not implemented yet.");
                break;
        }
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
     * Extract the body from a multipart message.
     *
     * Parse the multipart formatted email request and extract the body.
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     *   The request object. Must be a POST (or PUT) request.
     *
     * @return string
     *   The email body, in plain text.
     */
    protected function extractMultipartBody(Request $request)
    {
        if ($request->request->get('plain', FALSE)) {
            // Normalize new-lines.
            return str_replace(array("\r\n", "\r"), "\n", trim($request->request->get('plain')));
        } else {
            if ($request->request->get('html', FALSE)) {
                $html = $request->request->get('html');

                // Normalize new lines.
                $html = str_replace(array("\r\n", "\r"), "\n", $html);

                // Add new lines after all closing divs or paragraphs, to simulate
                // plain new lines.
                $html = preg_replace('/<\/div\s*>\n*/i', "</div>\n", $html);
                $html = preg_replace('/<\/p\s*>\n*/i', "</p>\n\n", $html);

                // Replace brs with newlines.
                $html = preg_replace('/<br\s*\/?>\n*/i', "\n", $html);

                // Remove all tags.
                $html = trim(strip_tags($html));

                // We assume we never have more than 2 subsequent new lines.
                // Markdown wouldn't parse it like that anyway.
                $html = preg_replace('/\n{3,}/', "\n\n", $html);

                return $html;
            } else {
                // We treat this as an empty body.
                return '';
            }
        }
    }

    /**
     * Extract the "Subject" header information.
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     *   The request object. Must be a POST (or PUT) request.
     *
     * @return string
     *
     * @throws Exception
     *   If the request contains no headers, it will throw an error.
     */
    protected function extractMultipartSubject(Request $request)
    {
        if ($headers = $request->request->get('headers', FALSE)) {
            if (!empty($headers['Subject'])) {
                return trim($headers['Subject']);
            } else {
                return '';
            }
        }

        throw new \Exception("No headers found.");
    }

    /**
     * Extract the "From" header information.
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     *   The request object. Must be a POST (or PUT) request.
     *
     * @return array
     *   An associative array, with the following keys:
     *   - raw: The raw From header.
     *   - name: The name of the sender, or an empty string.
     *   - address: The address of the sender.
     *
     * @throws Exception
     *   If the request contains no From header, it will throw an error.
     */
    protected function extractMultipartFrom(Request $request)
    {
        if ($headers = $request->request->get('headers', FALSE)) {
            if (!empty($headers['From'])) {
                $result = array(
                    'raw' => trim($headers['From']),
                );

                $result['name'] = $this->extractRecipientName($result['raw']);

                $result['address'] = $this->extractRecipientAddress($result['raw']);

                return $result;
            }
        }

        throw new \Exception("No headers found.");
    }

    /**
     * Extract the "Message-ID" header information.
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     *   The request object. Must be a POST (or PUT) request.
     *
     * @return string
     *
     * @throws Exception
     *   If the request contains no headers, it will throw an error.
     */
    protected function extractMultipartMessageID(Request $request)
    {
        if ($headers = $request->request->get('headers', FALSE)) {
            if (!empty($headers['Message-ID'])) {
                return trim($headers['Message-ID']);
            } else {
                return '';
            }
        }

        throw new \Exception("No headers found.");
    }

    /**
     * Extract the "CC" header information.
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     *   The request object. Must be a POST (or PUT) request.
     *
     * @return string
     *
     * @throws Exception
     *   If the request contains no headers, it will throw an error.
     */
    protected function extractMultipartRecipients(Request $request)
    {
        if ($headers = $request->request->get('headers', FALSE)) {
            if (!empty($headers['Cc'])) {
                return trim($headers['Cc']);
            } else {
                return '';
            }
        }

        throw new \Exception("No headers found.");
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
