<?php

namespace Sip\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Silex\Application;
use Sip\Forms\QuestionForm;

class QuestionController
{
    public function newEdit(Request $request, Application $app, $questionId=Null)
    {
        $questionForm = new QuestionForm();

        if ($questionForm->fillFromRequest($request)) {

        }
        else {
            $rs = $questionForm->read($app, $questionId);
        }

        if ($rs['result'] == 'abort') {
            $app->abort(404);
        }

        return $app['twig']->render(
            'question/new-edit.twig',
            array(
                'form' => $questionForm
            )
        );
    }

    public function getSentences(Request $request, Application $app)
    {
        return '';
    }
}