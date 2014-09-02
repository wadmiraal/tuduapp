<?php

/**
 * @file
 * Unit tests for the Parser class.
 */

namespace Tudu\Tests\Util;

use Tudu\Util\Parser;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test the parsing of email subjects.
     */
    public function testEmailSubjectParsing()
    {
        // Test extracting the list ID.
        $tests = array(
            'This is a subject' => null,
            'This is a subject [id:asldjkasd.asdklj]' => 'asldjkasd.asdklj',
            'This is a strange [id:asd9087asdhjkasd.9087asd] subject' => 'asd9087asdhjkasd.9087asd',
            'This one has è"èéàèé/&/&%75asd in it' => null,
            'This one has è"èéàèé/&/&%75asd [id:asd890asd089asd] in it' => 'asd890asd089asd',
            'This one has è"èéàèé/&/&%75asd [id:asd8.90a.s.d089asd] in it' => 'asd8.90a.s.d089asd',
        );

        foreach ($tests as $string => $expected) {
            $this->assertEquals($expected, Parser::extractTodoID($string), "Test extracting the todo ID from '$string'.");
        }
    }

    /**
     * Test the parsing of email bodies.
     */
    public function testEmailBodyParsing()
    {
        // Test extracting the email action.
        $tests = array(
            'This is a body' => Parser::COMMENT,
            'Add nothing' => Parser::COMMENT,
            'ADD: Task name' => Parser::ADD,
            'add: Task name' => Parser::ADD,
            'add   :  Task name' => Parser::ADD,
            'aDd   :  Task name' => Parser::ADD,
            "add   :\nTask name" => Parser::ADD,
            "ADD:\nTask name" => Parser::ADD,
            "Add:Task name" => Parser::ADD,
            "Delete a task" => Parser::COMMENT,
            "delete a" => Parser::COMMENT,
            "delete     0" => Parser::DELETE,
            "Delete     1022240" => Parser::DELETE,
            "DelEte 2" => Parser::DELETE,
            "DELETE122" => Parser::DELETE,
            "Delete1022240" => Parser::DELETE,
            "Done a task" => Parser::COMMENT,
            "done a" => Parser::COMMENT,
            "done     0" => Parser::DONE,
            "Done     1022240" => Parser::DONE,
            "DoNe 2" => Parser::DONE,
            "DONE122" => Parser::DONE,
            "Done1022240" => Parser::DONE,
            "Reset a task" => Parser::COMMENT,
            "reset a" => Parser::COMMENT,
            "reset     0" => Parser::RESET,
            "Reset     1022240" => Parser::RESET,
            "ReSet 2" => Parser::RESET,
            "RESET122" => Parser::RESET,
            "Reset1022240" => Parser::RESET,
        );

        foreach ($tests as $string => $expected) {
            $this->assertEquals($expected, Parser::extractAction($string), "Test extracting the action from '$string'.");
        }
    }
}
