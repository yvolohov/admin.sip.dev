<?php

namespace Sip\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Silex\Application;

class AssistantController
{
    public function assistant(Request $request, Application $app)
    {
        return $app['twig']->render('assistant/assistant.twig');
    }
}