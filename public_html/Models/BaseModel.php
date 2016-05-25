<?php

namespace Sip\Models;

class BaseModel
{
	private $db = Null;

    public function __construct($db)
    {
        $this->db = $db;
    }

    public function getDB()
    {
        return $this->db;
    }
}