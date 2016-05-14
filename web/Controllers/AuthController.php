<?php

namespace Sip\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Silex\Application;

class AuthController
{
    public function login(Request $request, Application $app)
    {
        return $app['twig']->render(
            'auth/login.twig'
        );
    }

    public function logout(Request $request, Application $app)
    {
        return 'logout';
    }
}