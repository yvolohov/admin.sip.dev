<?php

namespace Sip\Models;

class AuthModel extends BaseModel
{
    public function __construct($db)
    {
        parent::__construct($db);
    }

    public function getUser($login)
    {
        return $this->getDB()->fetchAssoc(
            'SELECT * FROM users WHERE login = :login',
            array(
                'login' => $login
            )
        );
    }
}