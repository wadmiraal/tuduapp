<?php

/**
 * @file
 * Abstract Model test class.
 *
 * Has some common functionality useful for all model unit tests.
 */

namespace Tudu\Tests\Model;

class AbstractModelTestClass extends \PHPUnit_Framework_TestCase
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
     * Create the database itself. If no filename is provided, generate a
     * random, unique name for the Sqlite database file.
     *
     * @param string $dbFile = null
     *   The name of the database file.
     */
    protected function setUp($dbFile = null)
    {
        $this->dbFile = isset($dbFile) ? $dbFile : __DIR__ . '/../../../../.tmp/' . uniqid() . '.db';

        if (!file_exists(__DIR__ . '/../../../../.tmp')) {
            mkdir(__DIR__ . '/../../../../.tmp');
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
        $todosTable->addColumn('description', 'text');
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
     * Return the test database driver.
     *
     * Our tests require an active database. We create a test Sqlite database
     * on setup. We connect to that one. On teardown, the database is removed.
     *
     * @return \Doctrine\DBAL\Connection
     */
    public function getDBDriver()
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
