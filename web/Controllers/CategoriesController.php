<?php

namespace Sip\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Silex\Application;
use Sip\Models\CategoriesModel;

class CategoriesController
{
    public function showTree(Request $request, Application $app)
    {
        $model = new CategoriesModel($app['db']);
        $categoriesDict = $model->getCategoriesDictionary();

        return $app['twig']->render(
            'categories/show-tree.twig',
            array(
                'key' => 0,
                'categories' => $categoriesDict
            )
        );
    }

    public function editTree(Request $request, Application $app)
    {
        $model = new CategoriesModel($app['db']);
        $categoriesDict = $model->getCategoriesDictionary();

        return $app['twig']->render(
            'categories/edit-tree.twig',
            array(
                'key' => 0,
                'categories' => $categoriesDict
            )
        );
    }
}