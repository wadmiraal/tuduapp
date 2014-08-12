<?php

/**
 * @file
 * Todo list representation.
 *
 * This class is responsible for persisting the data regarding the todo list.
 */

namespace Tudu\Model;

class Todo
{
    protected $db;
    protected $id;
    protected $owner;
    protected $lastUpdated;
    protected $created;
    protected $title;
    protected $notify;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function setOwner($owner)
    {
        $this->owner = $owner;
    }

    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function setNotify($notify)
    {
        $this->notify = $notify;
    }

    public function save() {
        if (!empty($this->id)) {
            // Update.
            $this->lastUpdated = date();
        } else {
            // Create.
            $this->id = str_replace('.', '-', uniqid(md5(rand()), true));
            $this->lastUpdated = 0;
            $this->created = date();
        }
    }
}
