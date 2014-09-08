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
     * @var string $description
     *   The description text of the list.
     */
    protected $description;

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
     * @var array $tasks
     *   A list of Tudu\Model\Task objects.
     */
    protected $tasks;

    /**
     * Constructor.
     *
     * @param \Doctrine\DBAL\Connection $connection
     *   The database connection object.
     */
    public function __construct(\Doctrine\DBAL\Connection $connection)
    {
        $this->connection = $connection;
        $this->participants = array();
        $this->tasks = array();
    }

    /**
     * Get the ID of the list.
     *
     * @return string
     *   The ID of the list.
     */
    public function getID()
    {
        return $this->id;
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
     * Set the description for the list.
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get the description for the list.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set a flag whether to notify participants of the list.
     *
     * @param bool $notifyParticipants
     *   True to notify participants on the next cron run, false otherwise.
     */
    public function setNotifyParticipants($notifyParticipants)
    {
        $this->notifyParticipants = (bool) $notifyParticipants;
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
     * @param string $lastMessageID = null
     *   An optional last message ID. Defaults to null.
     */
    public function addParticipant($email, $name, $lastMessageID = null)
    {
        if (!empty($this->participants)) {
            foreach ($this->participants as $participant) {
                if ($participant->getEmail() === $email) {
                    return;
                }
            }
        } else {
            $this->participants = array();
        }

        $participant = new Participant($this->connection);

        if (!empty($this->id)) {
            $participant->setTodoID($this->id);
        }

        $participant->setEmail($email);
        $participant->setName($name);

        if ($lastMessageID) {
            $participant->setLastMessageID($lastMessageID);
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
     * Add a task to the list.
     *
     * @param string $description
     *   The description of the task.
     * @param string $metaDue = null
     *   An optional due date of the task, if any. Defaults to null.
     * @param string $metaAssignedTo = null
     *   An optional name for the assigned person. Defaults to null.
     */
    public function addTask($description, $metaDue = null, $metaAssignedTo = null)
    {
        $task = new Task($this->connection);

        if (!empty($this->id)) {
            $task->setTodoID($this->id);
        }

        $task->setTask($description);
        
        if ($metaDue) {
            $task->setMetaDue($metaDue);
        }

        if ($metaAssignedTo) {
            $task->setMetaAssignedTo($metaAssignedTo);
        }

        $this->tasks[] = $task;
    }

    /**
     * Set a task "done" state.
     *
     * @param int $taskNum
     *   The number of the task.
     * @param bool $done
     *   The done flag.
     */
    public function setTaskState($num, $done)
    {
        $task = $this->getTask($num);
        if (!empty($task)) {
            $task->setDone($done);
        }
    }

    /**
     * Get the list of tasks.
     *
     * @return array
     *   An array of Tudu\Model\Task objects.
     */
    public function getTasks() {
        return $this->tasks;
    }

    /**
     * Get a specific task.
     *
     * @param int $num
     *   The number of the task.
     *
     * @return Tudu\Model\Task
     *
     * @throw \OutOfBoundsException
     */
    public function getTask($num)
    {
        if (!empty($this->tasks)) {
            foreach ($this->tasks as $task) {
                if ($task->getNum() == $num) {
                    return $task;
                }
            }
        } else {
            throw new \OutOfBoundsException("The task $num does not exist.");
        }
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
        $this->title = $data['title'];
        $this->description = $data['description'];
        $this->owner = $data['owner'];
        $this->created = $data['created'];
        $this->lastUpdated = $data['last_updated'];
        $this->notifyParticipants = (bool) $data['notify_participants'];

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

        if (!empty($rows)) {
            foreach ($rows as $row) {
                $participant = new Participant($this->connection);
                $participant->loadFromDBRow($row);
                $this->participants[] = $participant;
            }
        }

        // Load all tasks.
        $this->tasks = array();
        $stmt = $this->connection->prepare("
            SELECT  *
              FROM  tasks
             WHERE  todo_id = :id
          ORDER BY  num ASC
        ");
        $stmt->bindValue('id', $id);
        $stmt->execute();
        $rows = $stmt->fetchAll();

        if (!empty($rows)) {
            foreach ($rows as $row) {
                $task = new Task($this->connection);
                $task->loadFromDBRow($row);
                $this->tasks[] = $task;
            }
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
            $this->lastUpdated = date('Y-m-d H:i:00');

            $stmt = $this->connection->prepare("
                UPDATE  todos
                   SET  title = :title, description = :description, owner = :owner, last_updated = :last_updated, notify_participants = :notify_participants
                 WHERE  id = :id
            ");
        } else {
            $this->id = uniqid('', true);
            $this->created = $this->lastUpdated = date('Y-m-d H:i:00');

            if (empty($this->notifyParticipants)) {
                $this->notifyParticipants = false;
            }

            $stmt = $this->connection->prepare("
                INSERT INTO  todos
                     VALUES  (:id, :title, :description, :owner, :created, :last_updated, :notify_participants)
            ");
            $stmt->bindValue('created', $this->created);

            // Add the list ID to all participants.
            if (!empty($this->participants)) {
                foreach ($this->participants as $participant) {
                    $participant->setTodoId($this->id);
                }
            }

            // Add the list ID to all tasks.
            if (!empty($this->tasks)) {
                foreach ($this->tasks as $task) {
                    $task->setTodoId($this->id);
                }
            }
        }

        if (empty($this->description)) {
            $this->description = '';
        }

        // The other values are the same for both queries, which is why we bind
        // them here.
        $stmt->bindValue('id', $this->id);
        $stmt->bindValue('title', $this->title);
        $stmt->bindValue('description', $this->description);
        $stmt->bindValue('owner', $this->owner);
        $stmt->bindValue('last_updated', $this->lastUpdated);
        $stmt->bindValue('notify_participants', (int) $this->notifyParticipants);

        $stmt->execute();

        // Save all participants.
        if (!empty($this->participants)) {
            foreach ($this->participants as $participant) {
                $participant->save();
            }
        }

        // Save all tasks and assign the key in the tasks array as the task
        // number.
        if (!empty($this->tasks)) {
            foreach ($this->tasks as $task) {
                $task->save();
            }
        }
    }
}
