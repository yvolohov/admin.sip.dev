<?php

namespace Sip\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Silex\Application;
use Sip\Models\CategoriesModel;
use Sip\Models\QuestionsModel;

class QuestionsController
{
    public function show(Request $request, Application $app, $pageId=Null)
    {
    	$model = new QuestionsModel($app['db']);
        $paginator = $model->getQuestionsPaginator($pageId);
        $questionsList = $model->getQuestions($paginator['limitOffset'], $paginator['limitRows']);

        return $app['twig']->render(
            'questions/show.twig',
            array(
                'questions' => $questionsList,
                'paginator' => $paginator
            )
        );
    }

    public function showTree(Request $request, Application $app, $categoryId=Null)
    {
        $categoriesModel = new CategoriesModel($app['db']);
        $categoriesTreeData = $categoriesModel->getCategoriesDictionary();

        if ($categoryId == Null) {
            $categoryId = $categoriesModel->getRootCategoryId();
        }

        $guestionsModel = new QuestionsModel($app['db']);
        $questionsList = $guestionsModel->getQuestionsByCategory($categoryId);

        return $app['twig']->render(
            'questions/show-tree.twig',
            array(
                'key' => 0,
                'categories' => $categoriesTreeData,
                'categoryId' => $categoryId,
                'questions' => $questionsList
            )
        );
    }
}