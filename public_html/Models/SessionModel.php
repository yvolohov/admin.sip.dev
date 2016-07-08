<?php

namespace Sip\Models;

class SessionModel
{
    private $session = Null;

    public function __construct($session)
    {
        $this->session = $session;
    }

    public function getUser()
    {
        return $this->session->get('user');
    }

    public function setUser($user)
    {
        $this->session->set('user', $user);
    }

    public function hasUser()
    {
        return $this->session->has('user');
    }

    public function removeUser()
    {
        return $this->session->remove('user');
    }
}