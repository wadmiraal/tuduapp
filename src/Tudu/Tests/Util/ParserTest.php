<?php

/**
 * @file
 * Unit tests for the Parser class.
 */

namespace Tudu\Tests\Util;

use Tudu\Util\Parser;
use Tudu\Model\Todo;

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
            'Ad nothing' => array(Parser::COMMENT, 'Ad nothing'),
            'ADD Task name' => array(Parser::ADD, 'Task name'),
            'add Task name' => array(Parser::ADD, 'Task name'),
            'add     èlééàasdéè èsadé name' => array(Parser::ADD, 'èlééàasdéè èsadé name'),
            'aDd     Task name' => array(Parser::ADD, 'Task name'),
            "add   \nTask name\r\nOther stuff" => array(Parser::ADD, "Task name"),
            "ADD\nTask name" => array(Parser::ADD, 'Task name'),
            "Add  \n  Task name   \r Other line" => array(Parser::ADD, 'Task name'),
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

        $tests = array(
            'a simple string' => array(
                'string' => "- My task",
                'expected' => array(
                    '',
                    array('My task'),
                ),
            ),
            'a simple string, with different bullet points' => array(
                'string' => "- My task\n* My other task",
                'expected' => array(
                    '',
                    array(
                        'My task',
                        'My other task',
                    ),
                ),
            ),
            'a simple string, with UTF8 characters and Mac OS returns' => array(
                'string' => "- Mèïöôàüéä\r* M#°§èïöôàüéä",
                'expected' => array(
                    '',
                    array(
                        'Mèïöôàüéä',
                        'M#°§èïöôàüéä',
                    ),
                ),
            ),
            'a simple string with introduction text' => array(
                'string' => "Introduction\n-Task 1\r\n* Task 2",
                'expected' => array(
                    'Introduction',
                    array(
                        'Task 1',
                        'Task 2',
                    ),
                ),
            ),
            'a simple string with a multi paragraph introduction text' => array(
                'string' => "Introduction\nHi there\r\n\r\nAll of you\n- Task 1",
                'expected' => array(
                    "Introduction\nHi there\n\nAll of you",
                    array('Task 1'),
                ),
            ),
            'a simple string with a multi paragraph introduction text containing Markdown' => array(
                'string' => "Introduction\nHi* there*\r\n\r\n**All of you**\n- Task 1",
                'expected' => array(
                    "Introduction\nHi* there*\n\n**All of you**",
                    array('Task 1'),
                ),
            ),
            'a complex string, with multiple list styles and text in between.' => array(
                'string' => "This is my text right here.

asjkd hsad989w40932 jksd []]´fdèpe^Q0'E9 12ESKCXX <,A
sqàéds dfkélsdglè¨40t^3ot q43t7fsdcm:;>YXc dsackln ?`?öö!!!öàxcà.X%57SW%4¢#@°342342344




aséà dlàélad'^q3o¨àasc è@¦°#°¬§°|¬§¢¬§°¢°§¬¢°¬|\gkad hgz8tq49g3

- my listr
- asdlkj jklqd934698 §°#°§¢#¦¢#¢@#4234èééàààèé èèdècé[]}àD!ü£äF£SSFDafdWFewf
- élk sd?F`we^tffw sà<dév <sdfé '48 357 ztoiewfu ljéSCSAC
* KLSCa98AF7932F CX,X<VC MDFHGKHAI98Q43'6^'457¨HGFM,B.B DF.fdg-GSD¨CASéDàAàSDS FG

* L0'ASD ''^12E{DéFàSDéG F'QWEF'P34¨FèSàC Cls aàDvdf d dsfsdf[][]

¨R
T^
ERAGA

asdl adsék léas
àéalkd ésalkd",
                'expected' => array(
                    "This is my text right here.

asjkd hsad989w40932 jksd []]´fdèpe^Q0'E9 12ESKCXX <,A
sqàéds dfkélsdglè¨40t^3ot q43t7fsdcm:;>YXc dsackln ?`?öö!!!öàxcà.X%57SW%4¢#@°342342344




aséà dlàélad'^q3o¨àasc è@¦°#°¬§°|¬§¢¬§°¢°§¬¢°¬|\gkad hgz8tq49g3",
                    array(
                        'my listr',
                        'asdlkj jklqd934698 §°#°§¢#¦¢#¢@#4234èééàààèé èèdècé[]}àD!ü£äF£SSFDafdWFewf',
                        "élk sd?F`we^tffw sà<dév <sdfé '48 357 ztoiewfu ljéSCSAC",
                        "KLSCa98AF7932F CX,X<VC MDFHGKHAI98Q43'6^'457¨HGFM,B.B DF.fdg-GSD¨CASéDàAàSDS FG",
                        "L0'ASD ''^12E{DéFàSDéG F'QWEF'P34¨FèSàC Cls aàDvdf d dsfsdf[][]"
                    ),
                ),
            ),
        );

        foreach ($tests as $label => $data) {
            $this->assertEquals($data['expected'], Parser::extractTodoList($data['string']), "Test extracting information from $label.");
        }
    }

    /**
     * Test parsing task meta data.
     */
    public function testTaskMetaData()
    {
        $todo = new Todo(\Doctrine\DBAL\DriverManager::getConnection(array(
            'driver' => 'pdo_sqlite',
        ), new \Doctrine\DBAL\Configuration()));
        $todo->addParticipant('john@doe.com', 'John Doe');
        $todo->addParticipant('janney@doe.com', '');
        $todo->addParticipant('ben@email.co.uk', 'Benji');
        $todo->addParticipant('jimmy.james@gmail.com', 'Jim');

        $tests = array(
            'a task with to meta data' => array(
                'string' => 'Task',
                'expected' => array(
                    'due'        => '',
                    'assignedTo' => '',
                ),
            ),
            'a task with a due date, in US format' => array(
                'string' => 'Task [due: Sep 1st, 2014]',
                'expected' => array(
                    'due'        => '2014-09-01 00:00:00',
                    'assignedTo' => '',
                ),
            ),
            'a task with a due date, saying "tomorrow"' => array(
                'string' => 'Task [due: tomorrow]',
                'expected' => array(
                    'due'        => date('Y-m-d 00:00:00', strtotime('now +1day')),
                    'assignedTo' => '',
                ),
            ),
            'a task with a assigned to name' => array(
                'string' => 'Task [assigned to:  Walter]',
                'expected' => array(
                    'due'        => '',
                    'assignedTo' => 'Walter',
                ),
            ),
            'a task with a assigned to email' => array(
                'string' => 'Task [assigned to: wad@wad.com]',
                'expected' => array(
                    'due'        => '',
                    'assignedTo' => 'wad@wad.com',
                ),
            ),
            'a task with a assigned to name from the participants' => array(
                'string' => 'Task [assigned to:  Benji]',
                'expected' => array(
                    'due'        => '',
                    'assignedTo' => 'ben@email.co.uk',
                ),
            ),
            'a task with a assigned to name from the participants, but with in lowercase' => array(
                'string' => 'Task [assigned to:  benji]',
                'expected' => array(
                    'due'        => '',
                    'assignedTo' => 'ben@email.co.uk',
                ),
            ),
            'a task with a assigned to name from the participants, but from the email' => array(
                'string' => 'Task [assigned to:  Jane]',
                'expected' => array(
                    'due'        => '',
                    'assignedTo' => 'janney@doe.com',
                ),
            ),
            'a task with a assigned to name and a date, with lots of spacing' => array(
                'string' => 'Task [  assigned to:  benji  ]   [  due:   August  2014  ]  ',
                'expected' => array(
                    'due'        => '2014-08-01 00:00:00',
                    'assignedTo' => 'ben@email.co.uk',
                ),
            ),
        );

        foreach ($tests as $label => $data) {
            list($metaDue, $metaAssignedTo) = Parser::extractTaskMeta($data['string'], $todo);
            $this->assertEquals($data['expected']['due'], $metaDue, "Parsing $label gets the correct due date.");
            $this->assertEquals($data['expected']['assignedTo'], $metaAssignedTo, "Parsing $label gets the correct assigned email.");
        }
    }
}
