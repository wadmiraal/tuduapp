<?php

/**
 * @file
 * Todo model unit tests.
 */

namespace Tudu\Tests\Model;

use Tudu\Model\Todo;
use Tudu\Model\Participant;

class TodoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Doctrine\DBAL\Connection $connection 
     *   The Doctrine database driver used during the tests.
     */
    protected $connection;

    /**
     * @var string $dbFile
     *   The name of the file containing the Sqlite database.
     */
    protected $dbFile;

    /**
     * Set up.
     *
     * Generate a random, unique name for the Sqlite database file.
     */
    protected function setUp()
    {
        $this->dbFile = __DIR__ . '.tmp/' . uniqid() . 'db';
    }

    /**
     * Tear down.
     *
     * Remove the Sqlite database file.
     */
    protected function tearDown()
    {
        @unlink($this->dbFile);
    }

    /**
     * Test the setters and getters.
     */
    public function testSettersAndGetters()
    {
        $todo = new Todo($this->getDBDriver());
        $todo->setTitle('title');
        $this->assertEquals('title', $todo->getTitle(), 'Test set/get title.');
        $todo->setOwner('owner@email.com');
        $this->assertEquals('owner@email.com', $todo->getOwner(), 'Test set/get owner.');
        $todo->setLastUpdated(date('Y-m-d H:i:s'));
        $this->assertEquals(date('Y-m-d H:i:s'), $todo->getLastUpdated(), 'Test set/get last updated.');
        $todo->setCreated(date('Y-m-d H:i:s'));
        $this->assertEquals(date('Y-m-d H:i:s'), $todo->getCreated(), 'Test set/get created.');
        $todo->setNotifyParticipants(1);
        $this->assertEquals(1, $todo->getNotifyParticipants(), 'Test set/get notify.');
    }

    /**
     * Test adding participants.
     */
    public function testAddParticipants()
    {
        $todo = new Todo($this->getDBDriver());
        $participant = new Participant($this->getDBDriver());
        $participant->setEmail('email@email.com');
        $participant->setName('Jane Doe');
        $participant->setLastMessageID('asd');

        $todo->addParticipant('email@email.com', 'Jane Doe', 'asd');
        $this->assertEquals(array(
            $participant,
        ), $todo->getParticipants(), 'Test adding a new participant.');

        $todo->addParticipant('email@email.com', 'Jane Doe 2', 'asd');
        $this->assertEquals(array(
            $participant,
        ), $todo->getParticipants(), 'Test adding an existing participant, with a different name.');

        $participant2 = new Participant($this->getDBDriver());
        $participant2->setEmail('john@doe.com');
        $participant2->setName('John Doe');
        $participant2->setLastMessageID('asd2');

        $todo->addParticipant('john@doe.com', 'John Doe', 'asd2');
        $this->assertEquals(array(
            $participant,
            $participant2,
        ), $todo->getParticipants(), 'Test adding another participant.');
    }

    /**
     * Test loading data from the database.
     */
    public function testLoading()
    {

    }

    /**
     * Return the test database driver.
     *
     * Our tests require an active database. We create a test Sqlite database
     * on setup. We connect to that one. On teardown, the database is removed.
     *
     * @return \Doctrine\DBAL\Connection
     */
    protected function getDBDriver()
    {
        if (!isset($this->connection)) {
            $this->connection = \Doctrine\DBAL\DriverManager::getConnection(array(
                'driver' => 'pdo_sqlite',
                'path'   => $this->dbFile,
            ), new \Doctrine\DBAL\Configuration());
        }

        return $this->connection;
    }
}
