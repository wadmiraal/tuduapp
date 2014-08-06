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
     * The way the data way given through POST can be set through the $mode
     * parameter.
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     *   The request object. Must be a POST (or PUT) request.
     * @param string $mode
     *   One of CloudMailinEmail::MULTIPART, CloudMailinEmail::JSON or
     *   CloudMailinEmail::RAW
     *
     * @throws Exception
     *   If the passed mode is not implemented or unknown, will throw an error.
     */
    public function __construct(Request $request, $mode = CloudMailinEmail::MULTIPART)
    {
        switch ($mode) {
            case CloudMailinEmail::MULTIPART:
                $this->body = $this->extractMultipartBody($request);
                break;

            default:
                throw new Exception("Mode is not available or not implemented yet.");
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

}
