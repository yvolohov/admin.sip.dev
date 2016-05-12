<?php

namespace Sip\Controllers;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Silex\Application;
use Sip\Forms\QuestionForm;
use Sip\Lib\SentencesBuilder;

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
        $returnStructure = array(
            'sentences' => array(),
            'errors' => array()
        );

        $postData = $request->request->all();
        $templates = \Sip\Lib\parseRequest($postData, 'templates');

        foreach ($templates as $template) {

            $sentencesStructure = SentencesBuilder::getSentencesTableWithTokens(
                $template['foreign_template'],
                $template['native_template']
            );

            if ($sentencesStructure['success']) {

                foreach ($sentencesStructure['data'] as $sentence) {
                    $returnStructure['sentences'][] = $sentence;
                }
            }
            else {
                $returnStructure['errors'][] = $sentencesStructure['error_description'];
            }
        }

        return new JsonResponse($returnStructure);
    }
}