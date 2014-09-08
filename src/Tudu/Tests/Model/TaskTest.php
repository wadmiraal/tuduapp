<?php

/**
 * @file
 * Task model unit tests.
 */

namespace Tudu\Tests\Model;

use Tudu\Tests\Model\AbstractModelTestClass;
use Tudu\Model\Task;

class TaskTest extends AbstractModelTestClass
{
    /**
     * Test the setters and getters.
     */
    public function testSettersAndGetters()
    {
        $task = new Task($this->getDBDriver());
        $task->setTodoID('sadpoi');
        $this->assertEquals('sadpoi', $task->getTodoID(), 'Test set/get todo ID.');
        $task->setTask('Do stuff');
        $this->assertEquals('Do stuff', $task->getTask(), 'Test set/get task description.');
        $task->setNum(2);
        $this->assertEquals(2, $task->getNum(), 'Test set/get number.');
        $task->setDone(true);
        $this->assertEquals(true, $task->getDone(), 'Test set/get done state.');
        $task->setMetaDue('2014-03-02');
        $this->assertEquals('2014-03-02', $task->getMetaDue(), 'Test set/get due date meta data.');
        $task->setMetaAssignedTo('Jim');
        $this->assertEquals('Jim', $task->getMetaAssignedTo(), 'Test set/get assigned person meta data.');
    }

    /**
     * Test persisting data to the database.
     */
    public function testDB()
    {
        // Test saving.
        $task = new Task($this->connection);
        $task->setTodoID('ID');
        $task->setTask('Do something');
        $task->setDone(true);
        $task->setMetaDue('Tomorrow');
        $task->setMetaAssignedTo('John Doe');
        $task->save();

        // Test loading.
        $task2 = new Task($this->connection);
        $stmt = $this->connection->prepare("
            SELECT  *
              FROM  tasks
             WHERE  todo_id = :id AND num = :num
        ");
        $stmt->bindValue('id', $task->getTodoID());
        $stmt->bindValue('num', $task->getNum());
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $task2->loadFromDBRow($rows[0]);
        $this->assertEquals($task, $task2, 'Loading loads the same attributes.');

        // Test removing.
        $task2->remove();
        $stmt = $this->connection->prepare("
            SELECT  *
              FROM  tasks
             WHERE  todo_id = :id AND num = :num
        ");
        $stmt->bindValue('id', $task->getTodoID());
        $stmt->bindValue('num', $task->getNum());
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $this->assertEquals(0, count($rows), 'Removing the task deletes it from the database.');
    }
}
