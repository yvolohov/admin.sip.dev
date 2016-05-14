<?php

namespace Sip\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Silex\Application;
use Sip\Forms\LoginForm;

class AuthController
{
    public function login(Request $request, Application $app)
    {
        $loginForm = new LoginForm();
        $formData = $request->request->get($loginForm->getFormName());

        if (is_array($formData)) {

        }

        return $app['twig']->render(
            'auth/login.twig',
            array(
                'form' => $loginForm
            )
        );
    }

    public function logout(Request $request, Application $app)
    {
        return 'logout';
    }
}