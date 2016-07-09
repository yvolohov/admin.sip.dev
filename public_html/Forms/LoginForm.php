<?php

namespace Sip\Forms;

use Symfony\Component\Validator\Constraints as Assert;
use Sip\Models\AuthModel;

class LoginForm extends BaseForm
{
    public function __construct()
    {
        parent::__construct('login');
        $this->setField('email', 'Email', 'text', '', array(
            new Assert\Email()
        ));
        $this->setField('password', 'Пароль', 'password', array(
            new Assert\NotBlank()
        ));
    }

    public function validate($app, $formData)
    {
        if (!is_array($formData)) {
            return False;
        }

        $this->fillForm($formData);
        $authModel = new AuthModel($app['db']);
        $user = $authModel->getUser($this->getParam('email', 'value'));

        if ($user == Null) {
            $this->addError('Incorrect email or password');
            return False;
        }

        $email = trim($this->getParam('email', 'value'));
        $password = trim($this->getParam('password', 'value'));
        $hash = sha1(md5($password) . $email);

        if ($hash != $user['password']) {
            $this->addError('Incorrect email or password');
            return False;
        }

        return $user;
    }
}