<?php

/**
 * @file
 * Web tests for the Main controller.
 */

namespace Tudu\Tests\Controller;

use Tudu\Tests\Model\AbstractModelTestClass;
use Tudu\Util\Parser;
use Tudu\Model\Todo;
use Silex\WebTestCase;

class MainTest extends WebTestCase
{
    /**
     * {@inheritDoc}
     */
    public function createApplication()
    {
        $app = require __DIR__ . '/../app.php';

        // We use the asbtract model test class to create the database tables.
        $this->model = new AbstractModelTestClass();
        $this->model->setUp($app['conf']['db.options']['path']);

        return $app;
    }

    /**
     * Tear down.
     *
     * Remove the test database file.
     */
    protected function tearDown()
    {
        $this->model->tearDown();
    }

    /**
     * Text CloudMailin "new" list callback.
     */
    public function testCloudMailinNewInbox()
    {
        $client = $this->createClient();
        $tests = array(
            'Simple email' => array(
                'post' => array(
                    'plain' => "Introduction text\n\n- Simple email Task 1\n- Simple email Task 2\n\nSignature",
                    'headers' => array(
                        'From' => 'Test Bot <test@bot.com>',
                        'To'   => 'new@tuduapp.com',
                        'Cc'   => 'Jimmy <jimmy@test.com>, Jane <jane@doe.com>',
                        'Message-ID' => 'message--id',
                        'Subject'    => 'My new list',
                    ),
                ),
                'expected' => array(
                    'description' => 'Introduction text',
                    'tasks' => array(
                        'Simple email Task 1',
                        'Simple email Task 2',
                    ),
                    'participants' => array(
                        array(
                            'email' => 'test@bot.com',
                            'name'  => 'Test Bot',
                        ),
                        array(
                            'email' => 'jimmy@test.com',
                            'name'  => 'Jimmy',
                        ),
                        array(
                            'email' => 'jane@doe.com',
                            'name'  => 'Jane',
                        ),
                    ),
                ),
            ),
            'Complex email' => array(
                'post' => array(
                    'html' => "<div>Introduction text</div>\r\n\r\n<p><BR />With UTF8 chars: <strong>éèàäüöäôîç</strong><br><em>And special characters:|¬§°#¦~</em></p><p>And Markdown:**Ahoy** [name](http://link.com)</p>\n\n<br /><br />- Complex email Task 1<br /><div>* Complex email Task 2</div>Signature",
                    'headers' => array(
                        'From' => 'Test Bot <test@bot.com>',
                        'To'   => 'new@tuduapp.com',
                        'Cc'   => '',
                        'Message-ID' => 'message--id',
                        'Subject'    => 'My new list',
                    ),
                ),
                'expected' => array(
                    'description' => "Introduction text\n\nWith UTF8 chars: éèàäüöäôîç\nAnd special characters:|¬§°#¦~\n\nAnd Markdown:**Ahoy** [name](http://link.com)",
                    'tasks' => array(
                        'Complex email Task 1',
                        'Complex email Task 2',
                    ),
                    'participants' => array(
                        array(
                            'email' => 'test@bot.com',
                            'name'  => 'Test Bot',
                        ),
                    ),
                ),
            ),
        );
        foreach ($tests as $label => $data) {
            $crawler = $client->request('POST', '/inbox', $data['post']);
            $this->assertTrue($client->getResponse()->isSuccessful(), "Request is successful.");
            $this->assertContains('Todo list created', $client->getResponse()->getContent(), "The response contains the message saying the list was created.");

            $id = Parser::extractTodoID($client->getResponse()->getContent());
            $todo = new Todo($this->model->getDBDriver());
            $todo->load($id);

            $this->assertEquals($data['expected']['description'], $todo->getDescription(), "Set the correct description.");

            $participants = array();
            foreach ($todo->getParticipants() as $participant) {
                $participants[] = array(
                    'email' => $participant->getEmail(),
                    'name'  => $participant->getName(),
                );
            }

            $this->assertEquals($participants, $data['expected']['participants'], 'Added correct participants.');

            $tasks = array();
            foreach ($todo->getTasks() as $task) {
                $tasks[] = $task->getTask();
            }

            $this->assertEquals($tasks, $data['expected']['tasks'], 'Added correct tasks.');
        }
    }
}
