<?php

/**
 * @file
 * List participant representation.
 *
 * This class is responsible for persisting the data regarding the participants
 * for the todo list.
 */

namespace Tudu\Model;

class Participant
{
    /**
     * @var Doctrine\DBAL\Connection $connection
     *   The Doctrine DBAL connection object.
     */
    protected $connection;

    /**
     * @var string $id
     *   The UUID of the task list.
     */
    protected $todoId;

    /**
     * @var string $email
     *   The email of the participant.
     */
    protected $email;

    /**
     * @var string $name
     *   The parsed name of the participant.
     */
    protected $name;

    /**
     * @var string $lastMessageId
     *   The last message ID of the participant.
     */
    protected $lastMessageId;

    /**
     * Constructor.
     *
     * @param \Doctrine\DBAL\Connection $connection
     *   The database connection object.
     */
    public function __construct(\Doctrine\DBAL\Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * Set the ID of the list.
     *
     * @param string $todoId
     *   The ID of the list.
     */
    public function setTodoId($todoId)
    {
        $this->todoId = $todoId;
    }

    /**
     * Get the ID of the list.
     *
     * @return string
     *   The ID of the list.
     */
    public function getTodoId()
    {
        return $this->todoId;
    }

    /**
     * Set the email of the participant.
     *
     * @param string $email
     *   The email of the participant.
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * Get the email of the participant.
     *
     * @return string
     *   The email of the participant.
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the name of the participant.
     *
     * @param string $name
     *   The name of the participant.
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Get the name of the participant.
     *
     * @return string
     *   The name of the participant.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the last message ID of the participant.
     *
     * @param string $lastMessageId
     *   The last message ID of the participant.
     */
    public function setLastMessageId($lastMessageId)
    {
        $this->lastMessageId = $lastMessageId;
    }

    /**
     * Get the last message ID of the participant.
     *
     * @return string
     *   The last message ID of the participant.
     */
    public function getLastMessageId()
    {
        return $this->lastMessageId;
    }

    /**
     * Load the participant data from a database row.
     *
     * @param array $row
     *   The database row, in array format.
     */
    public function loadFromDBRow(array $row)
    {
        $this->todoId = $row['todo_id'];
        $this->email = $row['email'];
        $this->name = $row['name'];
        $this->lastMessageId = $row['last_message_id'];
    }

    /**
     * Save a participant.
     */
    public function save()
    {
        // To avoid duplicates, we remove past participants, if any, and insert
        // theme again.
        $stmt = $this->connection->prepare("
            DELETE FROM  participants
                  WHERE  email = :email AND todo_id = :todo_id
        ");
        $stmt->bindValue('todo_id', $this->todoId);
        $stmt->bindValue('email', $this->email);
        $stmt->execute();

        // Make sure we have a name and last message ID.
        if (empty($this->name)) {
            $this->name = $this->email;
        }

        if (empty($this->lastMessageId)) {
            $this->lastMessageId = 0;
        }

        $stmt = $this->connection->prepare("
            INSERT INTO  participants
                 VALUES  (:email, :todo_id, :name, :last_message_id)
        ");
        $stmt->bindValue('todo_id', $this->todoId);
        $stmt->bindValue('email', $this->email);
        $stmt->bindValue('name', $this->name);
        $stmt->bindValue('last_message_id', $this->lastMessageId);

        $stmt->execute();
    }
}
