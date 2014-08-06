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
        $plainText = array(
            'a simple, straight-forward email body' => array(
                'string'   => "Line 1\nLine2\n\nLine3",
                'expected' => "Line 1\nLine2\n\nLine3",
            ),
            'a simple, straight-forward email body with different new lines' => array(
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


            //'html'  => '<div dir=\"ltr\">Line 1<div>Line 2</div><p>Line 3</p><div><br></div></div>',
    }

}
