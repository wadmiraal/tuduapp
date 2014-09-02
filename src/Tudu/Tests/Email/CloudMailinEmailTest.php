<?php

/**
 * @file
 * Unit tests for the CloudMailinEmail class.
 */

namespace Tudu\Tests\Email;

use Tudu\Email\CloudMailinEmail;
use Symfony\Component\HttpFoundation\Request;

class CloudMailinEmailTest extends \PHPUnit_Framework_TestCase
{

    /**
     * Test the parsing of the email headers.
     */
    public function testEmailHeaderParsing()
    {
        $default = $this->getDefaultRequest();

        $rawFrom = array(
            'a simple raw header should return as is' => array(
                'string'   => 'john@doe.com',
                'expected' => 'john@doe.com',
            ),
            'a simple raw header should return as is, but trimmed' => array(
                'string'   => '  john@doe.com    ',
                'expected' => 'john@doe.com',
            ),
            'a complex raw header should return as is' => array(
                'string'   => '  "John Doe, The second" <john@doe.com>    ',
                'expected' => '"John Doe, The second" <john@doe.com>',
            ),
        );

        foreach ($rawFrom as $label => $data) {
            $default['headers']['From'] = $data['string'];
            $request = new Request(array(), $default);

            $email = new CloudMailinEmail($request);

            $this->assertEquals($data['expected'], $email->getFrom(), "Parsing $label.");
        }

        $fromAddress = array(
            'a simple from header should return the correct address' => array(
                'string'   => 'john@doe.com',
                'expected' => 'john@doe.com',
            ),
            'a simple from header should return the correct address, trimmed' => array(
                'string'   => '    john@doe.com      ',
                'expected' => 'john@doe.com',
            ),
            'a complex from header should return the correct address, with no <>' => array(
                'string'   => '  "John Doe, The second" <john@doe.com>    ',
                'expected' => 'john@doe.com',
            ),
            'a complex email address in the from header should return the correct address' => array(
                'string'   => '  "John Doe, The second" <john-doe._.--asd--AS09888987@doe.doe.doe.com>    ',
                'expected' => 'john-doe._.--asd--AS09888987@doe.doe.doe.com',
            ),
            'an incorrect email address in the from header should not be recognized' => array(
                'string'   => 'john-doe._.--asd--AS098?!88987@doe..doe.doe.com',
                'expected' => '',
            ),
            'a name resembling an email address in the from header should not be used instead of the actual address' => array(
                'string'   => 'john@doe.com <jane@doe.com>',
                'expected' => 'jane@doe.com',
            ),
        );

        foreach ($fromAddress as $label => $data) {
            $default['headers']['From'] = $data['string'];
            $request = new Request(array(), $default);

            $email = new CloudMailinEmail($request);

            $this->assertEquals($data['expected'], $email->getFromAddress(), "Parsing $label.");
        }

        $fromName = array(
            'a simple from header with no name should return an empty string' => array(
                'string'   => 'john@doe.com',
                'expected' => '',
            ),
            'a complex from header with no name should return an empty string' => array(
                'string'   => '  <john@doe.com>',
                'expected' => '',
            ),
            'a header with a simple name should just return the name' => array(
                'string'   => 'John Doe <john@doe.com>',
                'expected' => 'John Doe',
            ),
            'a header with a simple name should just return the name, no double quotes' => array(
                'string'   => '"John Doe" <john@doe.com>',
                'expected' => 'John Doe',
            ),
            'a header with a simple name should just return the name, no single quotes' => array(
                'string'   => "'John Doe' <john@doe.com>",
                'expected' => 'John Doe',
            ),
            'a header can that contains a name with special characters should work' => array(
                'string'   => '"John Doe, l\'asplééààéàéèt % ?/)(_--" <john@doe.com>',
                'expected' => 'John Doe, l\'asplééààéàéèt % ?/)(_--',
            ),
        );

        foreach ($fromName as $label => $data) {
            $default['headers']['From'] = $data['string'];
            $request = new Request(array(), $default);

            $email = new CloudMailinEmail($request);

            $this->assertEquals($data['expected'], $email->getFromName(), "Parsing $label.");
        }

        $subject = array(
            'a simple subject should return it verbatim' => array(
                'string'   => 'Subject',
                'expected' => 'Subject',
            ),
            'a simple subject should return it trimmed' => array(
                'string'   => '     Subject    ',
                'expected' => 'Subject',
            ),
            'a header with no subject should just return an empty string' => array(
                'string'   => null,
                'expected' => '',
            ),
        );

        foreach ($subject as $label => $data) {
            $default['headers']['Subject'] = $data['string'];
            $request = new Request(array(), $default);

            $email = new CloudMailinEmail($request);

            $this->assertEquals($data['expected'], $email->getSubject(), "Parsing $label.");
        }

        $messageID = array(
            'a simple message ID should return it verbatim' => array(
                'string'   => '890asdjhgkasf90-sdfsdffsdf--sdf8s@df089sdfsdf',
                'expected' => '890asdjhgkasf90-sdfsdffsdf--sdf8s@df089sdfsdf',
            ),
            'a simple message ID should return it trimmed' => array(
                'string'   => '     890asdjhgksdasdsaddasf90-sdfsdffsdf--sdf8s@df089sdfsdf    ',
                'expected' => '890asdjhgksdasdsaddasf90-sdfsdffsdf--sdf8s@df089sdfsdf',
            ),
            'a header with no message ID should just return an empty string' => array(
                'string'   => null,
                'expected' => '',
            ),
        );

        foreach ($messageID as $label => $data) {
            $default['headers']['Message-ID'] = $data['string'];
            $request = new Request(array(), $default);

            $email = new CloudMailinEmail($request);

            $this->assertEquals($data['expected'], $email->getMessageID(), "Parsing $label.");
        }

        $recipients = array(
            'a simple, empty CC header' => array(
                'string'   => '',
                'expected' => null,
            ),
            'a simple CC header, with only one recipient (no name)' => array(
                'string'   => 'john.doe@gmail.com',
                'expected' => array(array(
                    'address' => 'john.doe@gmail.com',
                    'name' => '',
                    'raw' => 'john.doe@gmail.com',
                )),
            ),
            'a simple CC header, with only one recipient (with name)' => array(
                'string'   => 'John Doe <john.doe@gmail.com>',
                'expected' => array(array(
                    'address' => 'john.doe@gmail.com',
                    'name' => 'John Doe',
                    'raw' => 'John Doe <john.doe@gmail.com>',
                )),
            ),
            'a complex CC header, with only one recipient (with name)' => array(
                'string'   => '              üéà-èàèéasd@asdjk <john.doe@gmail.com>  ',
                'expected' => array(array(
                    'address' => 'john.doe@gmail.com',
                    'name' => 'üéà-èàèéasd@asdjk',
                    'raw' => 'üéà-èàèéasd@asdjk <john.doe@gmail.com>',
                )),
            ),
            'a simple CC header, with multiple recipients (no name)' => array(
                'string'   => 'john.doe@gmail.com, jane.doe@gmail.com',
                'expected' => array(
                    array(
                        'address' => 'john.doe@gmail.com',
                        'name' => '',
                        'raw' => 'john.doe@gmail.com',
                    ),
                    array(
                        'address' => 'jane.doe@gmail.com',
                        'name' => '',
                        'raw' => 'jane.doe@gmail.com',
                    ),
                ),
            ),
            'a simple CC header, with multiple recipients (with name)' => array(
                'string'   => 'John Doe <john.doe@gmail.com>, Jane Doe <jane.doe@gmail.com>',
                'expected' => array(
                    array(
                        'address' => 'john.doe@gmail.com',
                        'name' => 'John Doe',
                        'raw' => 'John Doe <john.doe@gmail.com>',
                    ),
                    array(
                        'address' => 'jane.doe@gmail.com',
                        'name' => 'Jane Doe',
                        'raw' => 'Jane Doe <jane.doe@gmail.com>',
                    ),
                ),
            ),
            'a complex CC header, with multiple invalid recipients' => array(
                'string'   => 'John Doe <john.doe@gmail.com>,, jimmy carter, jim@jane, Jane Doe <jane.doe@gmail.com>',
                'expected' => array(
                    array(
                        'address' => 'john.doe@gmail.com',
                        'name' => 'John Doe',
                        'raw' => 'John Doe <john.doe@gmail.com>',
                    ),
                    array(
                        'address' => 'jane.doe@gmail.com',
                        'name' => 'Jane Doe',
                        'raw' => 'Jane Doe <jane.doe@gmail.com>',
                    ),
                ),
            ),
        );

        foreach ($recipients as $label => $data) {
            $default['headers']['Cc'] = $data['string'];
            $request = new Request(array(), $default);

            $email = new CloudMailinEmail($request);

            $this->assertEquals($data['expected'], $email->getRecipients(), "Parsing $label.");
        }
    }

    /**
     * Test the parsing of the email body.
     */
    public function testEmailBodyParsing()
    {
        $default = $this->getDefaultRequest(array('plain', 'html'));

        $request = new Request(array(), $default + array(
            'html'  => 'html',
            'plain' => 'plain',
        ));

        $email = new CloudMailinEmail($request);

        $this->assertEquals('plain', $email->getBody(), "Plain text emails always take precedence.");

        $plainText = array(
            'a simple, straight-forward email body' => array(
                'string'   => "Line 1\nLine2\n\nLine3",
                'expected' => "Line 1\nLine2\n\nLine3",
            ),
            'a simple, straight-forward email body with different newlines' => array(
                'string'   => "Line 1\rLine2\r\n\nLine3",
                'expected' => "Line 1\nLine2\n\nLine3",
            ),
            'an email body with trailing white-space' => array(
                'string'   => "Line 1\rLine2\r\n\nLine3    ",
                'expected' => "Line 1\nLine2\n\nLine3",
            ),
            'an email body with leading white-space' => array(
                'string'   => "    Line 1\rLine2\r\n\nLine3",
                'expected' => "Line 1\nLine2\n\nLine3",
            ),
            'an email body with leading and trailing white-space' => array(
                'string'   => "  \n\r  Line 1\rLine2\r\n\nLine3   \r  ",
                'expected' => "Line 1\nLine2\n\nLine3",
            ),
        );

        foreach ($plainText as $label => $data) {
            $request = new Request(array(), $default + array(
                'plain' => $data['string'],
            ));

            $email = new CloudMailinEmail($request);

            $this->assertEquals($data['expected'], $email->getBody(), "Parsing $label.");
        }

        $htmlText = array(
            'a simple, straight-forward email body, with only divs' => array(
                'string'   => "<div>Line 1</div><div>Line2</div><div>Line3</div>",
                'expected' => "Line 1\nLine2\nLine3",
            ),
            'an email body, with only divs and containing newlines as well' => array(
                'string'   => "<div>Line 1</div>\n<div>Line2</div>\n<div>Line3</div>",
                'expected' => "Line 1\nLine2\nLine3",
            ),
            'an email body, with only divs and containing different newlines' => array(
                'string'   => "<div>Line 1</div>\r\n<div>Line2</div>\r<div>Line3</div>",
                'expected' => "Line 1\nLine2\nLine3",
            ),
            'an email body, with only ps and containing different newlines' => array(
                'string'   => "<p>Line 1</p>\r\n<p>Line2</p>\r<p>Line3</p>",
                'expected' => "Line 1\n\nLine2\n\nLine3",
            ),
            'an email body containing brs' => array(
                'string'   => "Line 1<br>Line2<br />Line3",
                'expected' => "Line 1\nLine2\nLine3",
            ),
            'an email body containing brs and newlines' => array(
                'string'   => "Line 1<br>\n\n\nLine2<br />\nLine3",
                'expected' => "Line 1\nLine2\nLine3",
            ),
            'an email body, containing nested divs and ps' => array(
                'string'   => "<div><p>Line 1</p><div><div></div><p>Line2</p></div></div><div><div><p>Line3</p></div></div>",
                'expected' => "Line 1\n\nLine2\n\nLine3",
            ),
        );

        foreach ($htmlText as $label => $data) {
            $request = new Request(array(), $default + array(
                'html' => $data['string'],
            ));

            $email = new CloudMailinEmail($request);

            $this->assertEquals($data['expected'], $email->getBody(), "Parsing $label.");
        }
    }

    /**
     * Provide a default request representation.
     *
     * The CloudMailinEmail class will throw errors if certain keys are not
     * found in the request. Provide a default array of keys, which can be
     * overridden for each test's needs.
     *
     * @param array $ignoreKeys = array()
     *   An array of keys to ignore, if necessary.
     *
     * @return array
     *   The default request.
     */
    protected function getDefaultRequest(array $ignoreKeys = array())
    {
        $default = array(
            'plain'   => rand(),
            'html'    => '<div>' . rand() . '</div>',
            'headers' => array(
                'Date'       => date('D, d M Y H:i:s O'),
                'From'       => "John Doe, the second <john.doe-the_second@mail.com.me>",
                'To'         => "Jimmy Jameson <jim@mail.com>",
                'Cc'         => "Whallas <whallas.gromit@gmail>, Jane Eyre <jane.the-one-and-only@literature.co.uk>",
                'Message-ID' => rand(),
                'Subject'    => rand(),
            ),
        );

        if (!empty($ignoreKeys)) {
            foreach ($ignoreKeys as $key) {
                unset($default[$key]);
            }
        }

        return $default;
    }

}
