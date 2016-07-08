<?php

namespace Sip\Controllers;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;
use Sip\Forms\LoginForm;
use Sip\Models\SessionModel;

class AuthController
{
    public function login(Request $request, Application $app)
    {
        $loginForm = new LoginForm();
        $formData = $request->request->get($loginForm->getFormName());
        $user = $loginForm->validate($app, $formData);
        $sessionModel = new SessionModel($app['session']);

        if ($user) {
            $sessionModel->setUser($user);
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
        $sessionModel = new SessionModel($app['session']);
        $sessionModel->removeUser();
        return new RedirectResponse('/');
    }

    public function checkAuth(Request $request, Application $app)
    {
        $sessionModel = new SessionModel($app['session']);

        if (!$sessionModel->hasUser()) {
            return new RedirectResponse('/login');
        }
    }

    public function setTwigGlobals(Request $request, Application $app)
    {
        $sessionModel = new SessionModel($app['session']);
        $app['twig']->addGlobal('GL_USER_IS_LOGGED_IN', $sessionModel->hasUser());
    }
}