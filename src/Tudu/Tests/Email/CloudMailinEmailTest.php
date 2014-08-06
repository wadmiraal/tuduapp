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

    public function testEmailParsing()
    {
        $request = new Request(array(), array(
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
            $request = new Request(array(), array(
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
            $request = new Request(array(), array(
                'html' => $data['string'],
            ));

            $email = new CloudMailinEmail($request);

            $this->assertEquals($data['expected'], $email->getBody(), "Parsing $label.");
        }


            //'html'  => '<div dir=\"ltr\">Line 1<div>Line 2</div><p>Line 3</p><div><br></div></div>',
    }

}
