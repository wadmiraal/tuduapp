<?php

/**
 * @file
 * Todo list representation.
 *
 * This class is responsible for persisting the data regarding the todo list.
 */

namespace Tudu\Model;

use Tudu\Model\Participant;

class Todo
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
    protected $id;

    /**
     * @var string $owner
     *   The email address of the owner of the list.
     */
    protected $owner;

    /**
     * @var string $lastUpdated
     *   The date and time the list was last updated.
     */
    protected $lastUpdated;

    /**
     * @var string $created
     *   The date and time the list was created.
     */
    protected $created;

    /**
     * @var string $title
     *   The title of the list.
     */
    protected $title;

    /**
     * @var bool $notifyParticipants
     *   A flag telling whether to notify participants of any changes.
     */
    protected $notifyParticipants;

    /**
     * @var array $participants
     *   A list of Tudu\Model\Participant objects.
     */
    protected $participants;

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
     * Set the owner email address for the list.
     *
     * @param string $owner
     *   The owner email address.
     */
    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    /**
     * Get the owner email address for the list.
     *
     * @return string
     *   The owner email address.
     */
    public function getOwner()
    {
        return $this->owner;
    }

    /**
     * Set the creation date time for the list.
     *
     * @param string $created
     *   The creation date and time for the list.
     */
    public function setCreated($created)
    {
        $this->created = $created;
    }

    /**
     * Get the creation date time for the list.
     *
     * @return string
     *   The creation date and time for the list.
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set the last updated date time for the list.
     *
     * @param string $lastUpdated
     *   The last updated date and time for the list.
     */
    public function setLastUpdated($lastUpdated)
    {
        $this->lastUpdated = $lastUpdated;
    }

    /**
     * Get the last updated date time for the list.
     *
     * @return string
     *   The last updated date and time for the list.
     */
    public function getLastUpdated()
    {
        return $this->lastUpdated;
    }

    /**
     * Set the title for the list.
     *
     * @param string $title
     *   The title for the list.
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * Get the title for the list.
     *
     * @return string
     *   The title for the list.
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set a flag whether to notify participants of the list.
     *
     * @param bool $notifyParticipants
     *   True to notify participants on the next cron run, false otherwise.
     */
    public function setNotifyParticipants($notifyParticipants)
    {
        $this->notifyParticipants = $notifyParticipants;
    }

    /**
     * Get the flag whether to notify participants of the list.
     *
     * @return bool
     *   True to notify participants on the next cron run, false otherwise.
     */
    public function getNotifyParticipants()
    {
        return $this->notifyParticipants;
    }

    /**
     * Add a participant to the list.
     *
     * If the participant is already listed, it will be ignored.
     *
     * @param string $email
     *   The email of the participant.
     * @param string $name
     *   The name of the participant.
     * @param string $lastMessageId = null
     *   An optional last message ID. Defaults to null.
     */
    public function addParticipant($email, $name, $lastMessageId = null)
    {
        foreach ($this->participants as $participant) {
            if ($participant->getEmail() === $email) {
                return;
            }
        }

        $participant = new Participant($this->connection);

        if (!empty($this->id)) {
            $participant->setTodoId($this->id);
        }

        $participant->setEmail($email);
        $participant->setName($name);

        if ($lastMessageId) {
            $participant->setLastMessageId($lastMessageId);
        }

        $this->participants[] = $participant;
    }

    /**
     * Get the list of participants.
     *
     * @return array
     *   An array of Tudu\Model\Participant objects.
     */
    public function getParticipants() {
        return $this->participants;
    }

    /**
     * Load a list.
     *
     * Loads the specified list from the database.
     */
    public function load($id)
    {
        $stmt = $this->connection->prepare("
            SELECT  *
              FROM  todos
             WHERE  id = :id
        ");
        $stmt->bindValue('id', $id);
        $stmt->execute();
        $data = $stmt->fetch(\PDO::FETCH_ASSOC);

        $this->id = $id;
        $this->owner = $data['owner'];
        $this->created = $data['created'];
        $this->lastUpdated = $data['last_updated'];
        $this->notifyParticipants = $data['notify_participants'];

        // Load all participants.
        $this->participants = array();
        $stmt = $this->connection->prepare("
            SELECT  *
              FROM  participants
             WHERE  todo_id = :id
        ");
        $stmt->bindValue('id', $id);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        foreach ($rows as $row) {
            $participant = new Participant($this->connection);
            $participant->loadFromDBRow($row);
            $this->participants[] = $participant;
        }
    }

    /**
     * Save a list.
     *
     * Saves the list details, as well as all participants.
     */
    public function save()
    {
        if (!empty($this->id)) {
            $stmt = $this->connection->prepare("
                UPDATE  todos
                   SET  title = :title, owner = :owner, last_updated = :last_updated, notify_participants = :notify_participants
                 WHERE  id = :id
            ");
        } else {
            $this->id = uniqid();
            $this->created = $this->lastUpdated = date('Y-m-d H:i:s');

            if (empty($this->notifyParticipants)) {
                $this->notifyParticipants = 0;
            }

            $stmt = $this->connection->prepare("
                INSERT INTO  todos
                     VALUES  (:id, :title, :owner, :created, :last_updated, :notify_participants)
            ");
            $stmt->bindValue('created', $this->created);

            // Add the list ID to all participants.
            foreach ($this->participants as $participant) {
                $participant->setTodoId($this->id);
            }
        }

        // The other values are the same for both queries, which is why we bind
        // them here.
        $stmt->bindValue('id', $this->id);
        $stmt->bindValue('title', $this->title);
        $stmt->bindValue('owner', $this->owner);
        $stmt->bindValue('last_updated', $this->lastUpdated);
        $stmt->bindValue('notify_participants', (int) $this->notifyParticipants);

        $stmt->execute();

        // Save all participants.
        foreach ($this->participants as $participant) {
            $participant->save();
        }
    }
}
