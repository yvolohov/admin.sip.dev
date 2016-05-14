<?php

namespace Sip\Forms;

use Symfony\Component\Validator\Constraints as Assert;

class LoginForm extends BaseForm
{
    public function __construct()
    {
        parent::__construct('login');
        $this->setField('email', 'Email', 'text', '', array(
            new Assert\Email()
        ));
        $this->setField('password', 'Password', 'password', array(
            new Assert\NotBlank()
        ));
    }
}