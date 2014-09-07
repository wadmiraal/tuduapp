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
     * Generate a random, unique name for the Sqlite database file. Create the
     * database itself.
     */
    protected function setUp()
    {
        $this->dbFile = __DIR__ . '/.tmp/' . uniqid() . '.db';

        if (!file_exists(__DIR__ . '/.tmp')) {
            mkdir(__DIR__ . '/.tmp');
        }

        $this->connection = $this->getDBDriver();

        $schema = new \Doctrine\DBAL\Schema\Schema();

        // We don't use actual, correct types, but some that approach the
        // final ones. The reason is Sqlite has some limitations when using
        // Doctrine DBAL. We don't care much about performance anyway in our
        // tests - the queries must just work.
        $todosTable = $schema->createTable('todos');
        $todosTable->addColumn('id', 'string', array('length' => 100));
        $todosTable->addColumn('title', 'string', array('length' => 100));
        $todosTable->addColumn('owner', 'string', array('length' => 100));
        $todosTable->addColumn('created', 'string', array('length' => 40));
        $todosTable->addColumn('last_updated', 'string', array('length' => 40));
        $todosTable->addColumn('notify_participants', 'integer');
        $todosTable->setPrimaryKey(array('id'));

        $tasksTable = $schema->createTable('tasks');
        $tasksTable->addColumn('todo_id', 'string', array('length' => 100));
        $tasksTable->addColumn('num', 'integer');
        $tasksTable->addColumn('task', 'text');
        $tasksTable->addColumn('done', 'integer');
        $tasksTable->addColumn('meta_due', 'string', array('length' => 100));
        $tasksTable->addColumn('meta_assigned_to', 'string', array('length' => 255));

        $participantsTable = $schema->createTable('participants');
        $participantsTable->addColumn('todo_id', 'string', array('length' => 100));
        $participantsTable->addColumn('email', 'string', array('length' => 100));
        $participantsTable->addColumn('name', 'string', array('length' => 100));
        $participantsTable->addColumn('last_message_id', 'string', array('length' => 100));

        $platform = $this->connection->getDatabasePlatform();
        $queries = $schema->toSql($platform);

        foreach ($queries as $query) {
            $stmt = $this->connection->prepare($query);
            $stmt->execute();
        }
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
     * Test persisting data to the database.
     */
    public function testDB()
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
