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
    protected $messageId;

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
                $this->from = $this->extractMultipartFrom($request);
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
     * Extract the "From" header information.
     *
     *
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
                //    'raw' => $headers['From'],
                );

                $match = array();
                preg_match('/(.+)\s*</', $headers['From'], $match);
                //$result['name'] = !empty($match[1]) ? $match[1] : '';

                $match = array();
                preg_match('/[\w._%+-]+@[\w.-]+\.\w{2,4}/i', $headers['From'], $match);
                //$result['address'] = !empty($match[0]) ? $match[0] : '';

                return $result;
            }
        }

        throw new \Exception("No headers found.");
    }

}
