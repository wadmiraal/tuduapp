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
            'This is a body' => array(Parser::COMMENT, 'This is a body'),
            'Add nothing' => array(Parser::COMMENT, 'Add nothing'),
            'ADD: Task name' => array(Parser::ADD, 'Task name'),
            'add: Task name' => array(Parser::ADD, 'Task name'),
            'add   :  èlééàasdéè èsadé name' => array(Parser::ADD, 'èlééàasdéè èsadé name'),
            'aDd   :  Task name' => array(Parser::ADD, 'Task name'),
            "add   :\nTask name\r\nOther stuff" => array(Parser::ADD, "Task name"),
            "ADD:\nTask name" => array(Parser::ADD, 'Task name'),
            "Add:Task name   \r Other line" => array(Parser::ADD, 'Task name'),
            "Delete a task" => array(Parser::COMMENT, 'Delete a task'),
            "delete a\nasdasd\n\nasdasd" => array(Parser::COMMENT, "delete a\nasdasd"),
            "delete     0" => array(Parser::DELETE, 0),
            "Delete     1022240" => array(Parser::DELETE, 1022240),
            "DelEte 2" => array(Parser::DELETE, 2),
            "DELETE122" => array(Parser::DELETE, 122),
            "Delete1022240" => array(Parser::DELETE, 1022240),
            "Done a task" => array(Parser::COMMENT, 'Done a task'),
            "done a" => array(Parser::COMMENT, 'done a'),
            "done     0" => array(Parser::DONE, 0),
            "Done     1022240" => array(Parser::DONE, 1022240),
            "DoNe 2" => array(Parser::DONE, 2),
            "DONE122" => array(Parser::DONE, 122),
            "Done1022240" => array(Parser::DONE, 1022240),
            "Reset a task" => array(Parser::COMMENT, 'Reset a task'),
            "reset a" => array(Parser::COMMENT, 'reset a'),
            "reset     0" => array(Parser::RESET, 0),
            "Reset     1022240" => array(Parser::RESET, 1022240),
            "ReSet 2" => array(Parser::RESET, 2),
            "RESET122" => array(Parser::RESET, 122),
            "Reset1022240" => array(Parser::RESET, 1022240),
        );

        foreach ($tests as $string => $expected) {
            $this->assertEquals($expected, Parser::extractAction($string), "Test extracting the action from '$string'.");
        }
    }
}
