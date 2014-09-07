<?php

/**
 * @file
 * List task representation.
 *
 * This class is responsible for persisting the data regarding the tasks
 * for the todo list.
 */

namespace Tudu\Model;

class Task
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
     * @var int $num
     *   The number of the task, starting at 1.
     */
    protected $num;

    /**
     * @var string $task
     *   The description of the task.
     */
    protected $task;

    /**
     * @var bool $done
     *   Wether the task was accomplished.
     */
    protected $done;

    /**
     * @var string $metaDue
     *   The due date of the task.
     */
    protected $metaDue;

    /**
     * @var string $metaAssignedTo
     *   The name of the person assigned to the task.
     */
    protected $metaAssignedTo;

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
     * Set the number of the task.
     *
     * @param int $num
     *   The number of the task.
     */
    public function setNum($num)
    {
        $this->num = $num;
    }

    /**
     * Get the number of the task.
     *
     * @return int
     *   The number of the task.
     */
    public function getNum()
    {
        return $this->num;
    }

    /**
     * Set wether the task is done or not.
     *
     * @param bool $done
     *   Wether the task is accomplished.
     */
    public function setDone($done)
    {
        $this->done = $done;
    }

    /**
     * Get wether the task is accomplished.
     *
     * @return bool
     *   Wether the task is accomplished.
     */
    public function getDone()
    {
        return $this->done;
    }

    /**
     * Set the description of the task.
     *
     * @param string $task
     *   The description of the task.
     */
    public function setTask($task)
    {
        $this->task = $task;
    }

    /**
     * Get the description of the task.
     *
     * @return string
     *   The description of the task.
     */
    public function getTask()
    {
        return $this->task;
    }

    /**
     * Set the due date of the task.
     *
     * @param string $metaDue
     *   The due date of the task.
     */
    public function setMetaDue($metaDue)
    {
        $this->metaDue = $metaDue;
    }

    /**
     * Get the due date of the task.
     *
     * @return string
     *   The due date of the task.
     */
    public function getMetaDue()
    {
        return $this->metaDue;
    }

    /**
     * Assign the task to a participant.
     *
     * @param string $metaAssignedTo
     *   The name of the person responsible for the task.
     */
    public function setMetaAssignedTo($metaAssignedTo)
    {
        $this->metaAssignedTo = $metaAssignedTo;
    }

    /**
     * Get the person responsible for the task.
     *
     * @return string
     *   The person responsible for the task.
     */
    public function getMetaAssignedTo()
    {
        return $this->metaAssignedTo;
    }

    /**
     * Load the task data from a database row.
     *
     * @param array $row
     *   The database row, in array format.
     */
    public function loadFromDBRow(array $row)
    {
        $this->todoID = $row['todo_id'];
        $this->num = $row['num'];
        $this->task = $row['task'];
        $this->done = (bool) $row['done'];
        $this->metaDue = $row['meta_due'];
        $this->metaDueAssignedTo = $row['meta_assigned_to'];
    }

    /**
     * Save a participant.
     */
    public function save()
    {
        // To avoid duplicates, we check if the task already existed.
        $stmt = $this->connection->prepare("
            SELECT  *
              FROM  tasks
             WHERE  num = :num AND todo_id = :todo_id
        ");
        $stmt->bindValue('todo_id', $this->todoID);
        $stmt->bindValue('num', $this->num);
        $stmt->execute();
        $prev_data = $stmt->fetch(\PDO::FETCH_ASSOC);

        // Make sure we have some meta data.
        if (empty($this->metaDue)) {
            $this->metaDue = '';
        }
        if (empty($this->metaAssignedTo)) {
            $this->metaAssignedTo = '';
        }

        // Make sure we have a number.
        if (empty($this->num)) {
            $stmt = $this->connection->prepare("
                SELECT  num
                  FROM  tasks
                 WHERE  todo_id = :todo_id
              ORDER BY  num DESC
                 LIMIT  1
            ");
            $stmt->bindValue('todo_id', $this->todoID);
            $stmt->execute();
            $highest_num = $stmt->fetch(\PDO::FETCH_ASSOC);
            $this->num = (int) $highest_num['num'] + 1;
        }

        // If we already had an entry, we update. Else, we insert.
        if (!empty($prev_data['todo_id'])) {
            $stmt = $this->connection->prepare("
                UPDATE  tasks
                   SET  task = :task, done = :done, meta_due = :meta_due, meta_assigned_to = :meta_assigned_to
                 WHERE  todo_id = :todo_id AND num = :num
            ");
        } else {
            $stmt = $this->connection->prepare("
                INSERT INTO  tasks
                     VALUES  (:todo_id, :num, :task, :done, :meta_due, :meta_assigned_to)
            ");
        }

        $stmt->bindValue('todo_id', $this->todoID);
        $stmt->bindValue('num', $this->num);
        $stmt->bindValue('task', $this->task);
        $stmt->bindValue('done', $this->done);
        $stmt->bindValue('meta_due', $this->metaDue);
        $stmt->bindValue('meta_assigned_to', $this->metaAssignedTo);

        $stmt->execute();
    }
}
