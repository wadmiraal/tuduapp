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
use Tudu\Email\BaseEmail;
use Symfony\Component\HttpFoundation\Request;

class CloudMailinEmail extends BaseEmail
{

    const MULTIPART = 'multipart';
    const JSON = 'json';
    const RAW = 'raw';

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
                $this->to = $this->extractMultipartTo($request);
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
            return $this->normalizePlainBody($request->request->get('plain'));
        } else {
            if ($request->request->get('html', FALSE)) {
                $html = $request->request->get('html');

                return $this->normalizeHTMLBody($html);
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
     * Extract the "To" header email address.
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     *   The request object. Must be a POST (or PUT) request.
     *
     * @return string
     *   The To email address.
     *
     * @throws Exception
     *   If the request contains no From header, it will throw an error.
     */
    protected function extractMultipartTo(Request $request)
    {
        if ($headers = $request->request->get('headers', FALSE)) {
            if (!empty($headers['To'])) {
                return $this->extractRecipientAddress(trim($headers['To']));
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
     * @return array|null
     *
     * @throws Exception
     *   If the request contains no headers, it will throw an error.
     */
    protected function extractMultipartRecipients(Request $request)
    {
        if ($headers = $request->request->get('headers', FALSE)) {
            if (!empty($headers['Cc'])) {
                return array_values(array_filter(array_map(function($item) {
                    $recipient = array(
                        'raw' => trim($item),
                    );

                    // If the trimmed string is empty, we can just skip it.
                    if (!empty($recipient['raw'])) {
                        $recipient['address'] = $this->extractRecipientAddress($recipient['raw']);

                        // If there's no email address, we skip it.
                        if (!empty($recipient['address'])) {
                            $recipient['name'] = $this->extractRecipientName($recipient['raw']);
                            return $recipient;
                        } else {
                            return null;
                        }
                    } else {
                        return null;
                    }
                }, explode(',', $headers['Cc']))));
            } else {
                return null;
            }
        }

        throw new \Exception("No headers found.");
    }
}
