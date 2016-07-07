<?php

namespace Sip\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Silex\Application;
use Sip\Models\TestModel;

class TestController
{
    public function page(Request $request, Application $app)
    {
        return $app['twig']->render(
            'test/page.twig',
            array(
                'startUrl' => "/test/start/",
                'completeUrl' => "/test/complete/"
            )
        );
    }

    public function start(Request $request, Application $app, $categoryId=Null)
    {
        $model = new TestModel($app['db'], $app['session']);

        if ($categoryId == Null) {
            $returnStructure = $model->startTest(
                $app['config']['test_questions_count'],
                $app['config']['test_sentences_count']
            );
        }
        else {
            $returnStructure = $model->startTestByCategory(
                $categoryId,
                $app['config']['test_sentences_count']
            );
        }

        return new JsonResponse($returnStructure);
    }

    public function complete(Request $request, Application $app, $categoryId=Null)
    {
        $model = new TestModel($app['db'], $app['session']);
        $returnStructure = ($categoryId == Null)
            ? $model->completeTest()
            : $model->completeTestByCategory($categoryId);

        return new JsonResponse($returnStructure);
    }
}