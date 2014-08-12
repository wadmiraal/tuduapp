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
    protected $id;
    protected $owner;
    protected $lastUpdated;
    protected $created;
    protected $title;
    protected $notify;

    public function __construct($id = NULL)
    {
        if (!empty($id)) {
            $this->id = $id;
        }
    }
}
