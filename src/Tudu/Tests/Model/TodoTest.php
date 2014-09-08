<?php

/**
 * @file
 * Todo model unit tests.
 */

namespace Tudu\Tests\Model;

use Tudu\Tests\Model\AbstractModelTestClass;
use Tudu\Model\Todo;
use Tudu\Model\Participant;
use Tudu\Model\Task;

class TodoTest extends AbstractModelTestClass
{
    /**
     * Test the setters and getters.
     */
    public function testSettersAndGetters()
    {
        $todo = new Todo($this->getDBDriver());
        $todo->setTitle('title');
        $this->assertEquals('title', $todo->getTitle(), 'Test set/get title.');
        $todo->setDescription('Description');
        $this->assertEquals('Description', $todo->getDescription(), 'Test set/get description.');
        $todo->setOwner('owner@email.com');
        $this->assertEquals('owner@email.com', $todo->getOwner(), 'Test set/get owner.');
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
     * Test managing tasks.
     */
    public function testManagingTasks()
    {
        $todo = new Todo($this->getDBDriver());
        $task = new Task($this->getDBDriver());
        $task->setTask('Do something');

        $todo->addTask('Do something');
        $this->assertEquals(array(
            $task,
        ), $todo->getTasks(), 'Test adding a new task.');

        $task2 = new Task($this->getDBDriver());
        $task2->setTask('Do something else');
        $task2->setMetaDue(date('Y-m-d H:00:00'));
        $task2->setMetaAssignedTo('Jim');

        $todo->addTask('Do something else', date('Y-m-d H:00:00'), 'Jim');
        $this->assertEquals(array(
            $task,
            $task2,
        ), $todo->getTasks(), 'Test adding another task.');
    }

    /**
     * Test persisting data to the database.
     */
    public function testDB()
    {
        // Test saving.
        $todo = new Todo($this->connection);
        $todo->setTitle('title');
        $todo->setOwner('owner@email.com');
        $todo->addParticipant('email@email.com', 'John Doe', 'asd');
        $todo->addTask('Do something', 'Tomorrow', 'Jim');
        $todo->save();

        // Check save went as planned.
        $time = date('Y-m-d H:i:00');
        $this->assertNotNull($todo->getID(), 'Saving assigns an ID.');
        $this->assertEquals($time, $todo->getCreated(), 'Saving assigns a created date if none was specified.');
        $this->assertEquals($time, $todo->getLastUpdated(), 'Saving assigns a last updated date if none was specified.');

        // Test loading.
        $todo2 = new Todo($this->connection);
        $todo2->load($todo->getID());
        $this->assertEquals($todo, $todo2, 'Loading loads the same attributes.');

        $todo->addTask('Do something else');
        // Save to assign a number to the task.
        $todo->save();
        $todo->setTaskState(2, true);
        // Save again to persist the state.
        $todo->save();

        // Load again.
        $todo2->load($todo->getID());

        // Check.
        $this->assertEquals(false, $todo2->getTask(1)->getDone(), 'Test setting a task done state.');
        $this->assertEquals(true, $todo2->getTask(2)->getDone(), 'Test setting a task done state.');
    }
}
