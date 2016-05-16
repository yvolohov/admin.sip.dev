<?php

namespace Sip\Controllers;

use Symfony\Component\HttpFoundation\RedirectResponse;
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
            $app['session']->set('user', array());
            return new RedirectResponse('/');
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
        $app['session']->remove('user');
        return new RedirectResponse('/login');
    }

    public function checkAuth(Request $request, Application $app)
    {
        if (!$app['session']->has('user')) {
            return new RedirectResponse('/login');
        }
    }
}