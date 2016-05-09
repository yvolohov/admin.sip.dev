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
            'test/page.twig'
        );
    }

    public function start(Request $request, Application $app)
    {
        $model = new TestModel($app['db']);
        $returnStructure = $model->startTest(
            1,
            $app['config']['test_questions_count'],
            $app['config']['test_sentences_count']
        );

        return new JsonResponse($returnStructure);
    }

    public function complete(Request $request, Application $app)
    {
        $model = new TestModel($app['db']);
        $returnStructure = $model->completeTest(1);

        return new JsonResponse($returnStructure);
    }
}