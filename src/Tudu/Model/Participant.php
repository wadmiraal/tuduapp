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
    protected $todoID;

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
     * @var string $lastMessageID
     *   The last message ID of the participant.
     */
    protected $lastMessageID;

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
     * @param string $todoID
     *   The ID of the list.
     */
    public function setTodoID($todoID)
    {
        $this->todoID = $todoID;
    }

    /**
     * Get the ID of the list.
     *
     * @return string
     *   The ID of the list.
     */
    public function getTodoID()
    {
        return $this->todoID;
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
     * @param string $lastMessageID
     *   The last message ID of the participant.
     */
    public function setLastMessageID($lastMessageID)
    {
        $this->lastMessageID = $lastMessageID;
    }

    /**
     * Get the last message ID of the participant.
     *
     * @return string
     *   The last message ID of the participant.
     */
    public function getLastMessageID()
    {
        return $this->lastMessageID;
    }

    /**
     * Load the participant data from a database row.
     *
     * @param array $row
     *   The database row, in array format.
     */
    public function loadFromDBRow(array $row)
    {
        $this->todoID = $row['todo_id'];
        $this->email = $row['email'];
        $this->name = $row['name'];
        $this->lastMessageID = $row['last_message_id'];
    }

    /**
     * Save a participant.
     */
    public function save()
    {
        // To avoid duplicates, we check if the participant already existed.
        $stmt = $this->connection->prepare("
            SELECT  *
              FROM  participants
             WHERE  email = :email AND todo_id = :todo_id
        ");
        $stmt->bindValue('todo_id', $this->todoID);
        $stmt->bindValue('email', $this->email);
        $stmt->execute();
        $prev_data = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Make sure we have a name and last message ID.
        if (empty($this->name)) {
            $this->name = $this->email;
        }

        if (empty($this->lastMessageID)) {
            $this->lastMessageID = 0;
        }

        // If we already had an entry, we update. Else, we insert.
        if (!empty($prev_data['todo_id'])) {
            $stmt = $this->connection->prepare("
                UPDATE  participants
                   SET  name = :name, last_message_id = :last_message_id
                 WHERE  todo_id = :todo_id AND email = :email
            ");
        } else {
            $stmt = $this->connection->prepare("
                INSERT INTO  participants
                     VALUES  (:email, :todo_id, :name, :last_message_id)
            ");
        }

        $stmt->bindValue('todo_id', $this->todoID);
        $stmt->bindValue('email', $this->email);
        $stmt->bindValue('name', $this->name);
        $stmt->bindValue('last_message_id', $this->lastMessageID);

        $stmt->execute();
    }
}
