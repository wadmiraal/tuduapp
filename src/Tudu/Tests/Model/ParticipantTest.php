<?php

/**
 * @file
 * Participant model unit tests.
 */

namespace Tudu\Tests\Model;

use Tudu\Tests\Model\AbstractModelTestClass;
use Tudu\Model\Participant;

class ParticipantTest extends AbstractModelTestClass
{
    /**
     * Test the setters and getters.
     */
    public function testSettersAndGetters()
    {
        $participant = new Participant($this->getDBDriver());
        $participant->setTodoID('sadpoi');
        $this->assertEquals('sadpoi', $participant->getTodoID(), 'Test set/get todo ID.');
        $participant->setEmail('email@email.com');
        $this->assertEquals('email@email.com', $participant->getEmail(), 'Test set/get email.');
        $participant->setName('John Doe');
        $this->assertEquals('John Doe', $participant->getName(), 'Test set/get name.');
        $participant->setLastMessageID('asd');
        $this->assertEquals('asd', $participant->getLastMessageID(), 'Test set/get last message ID.');
    }

    /**
     * Test persisting data to the database.
     */
    public function testDB()
    {
        // Test saving.
        $participant = new Participant($this->connection);
        $participant->setTodoID('ID');
        $participant->setEmail('owner@email.com');
        $participant->setName('John Doe');
        $participant->save();

        // Test loading.
        $participant2 = new Participant($this->connection);
        $stmt = $this->connection->prepare("
            SELECT  *
              FROM  participants
             WHERE  todo_id = :id AND email = :email
        ");
        $stmt->bindValue('id', $participant->getTodoID());
        $stmt->bindValue('email', $participant->getEmail());
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $participant2->loadFromDBRow($rows[0]);
        $this->assertEquals($participant, $participant2, 'Loading loads the same attributes.');
    }
}
