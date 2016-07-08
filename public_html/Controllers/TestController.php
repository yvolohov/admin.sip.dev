<?php

namespace Sip\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Silex\Application;
use Sip\Models\TestModel;
use Sip\Models\SessionModel;

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
        $userId = $this->getUserId($app['session']);
        $model = new TestModel($app['db'], $userId);

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
        $userId = $this->getUserId($app['session']);
        $model = new TestModel($app['db'], $userId);
        $returnStructure = ($categoryId == Null)
            ? $model->completeTest()
            : $model->completeTestByCategory($categoryId);

        return new JsonResponse($returnStructure);
    }

    private function getUserId($session)
    {
        $sessionModel = new SessionModel($session);
        $user = $sessionModel->getUser();

        if (!is_array($user)) {
            return Null;
        }

        return (isset($user['id'])) ? $user['id'] : Null;
    }
}