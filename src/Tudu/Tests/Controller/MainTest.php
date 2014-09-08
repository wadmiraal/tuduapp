<?php

/**
 * @file
 * Web tests for the Main controller.
 */

namespace Tudu\Tests\Controller;

use Silex\WebTestCase;

class MainTest extends WebTestCase
{
    /**
     * {@inheritDoc}
     */
    public function createApplication()
    {
        return require __DIR__ . '/../app.php';
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

                ),
                'expected' => array(

                ),
            ),
        );
        foreach ($tests as $label => $data) {
            $crawler = $client->request('POST', '/inbox', $data['post']);
            $this->assertTrue($client->getResponse()->isOk());
            $this->assertContains('Todo list created', $client->getResponse()->getContent());
            $this->assertTrue(0, $client->getResponse()->getContent());
        }
    }
}
