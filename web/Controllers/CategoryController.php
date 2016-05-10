<?php

namespace Sip\Controllers;

use Symfony\Component\HttpFoundation\Request;
use Silex\Application;
use Sip\Models\CategoryModel;
use Sip\Forms\CategoryForm;

class CategoryController
{
    public function show(Request $request, Application $app, $categoryId, $categoryName=Null)
    {
        $model = new CategoryModel($app['db']);
        $category = $model->getCategoryById($categoryId);

        if ($category == Null) {
            $app->abort(404);
        }

        $categoryDBName = $category['url_name'];

        if ($categoryDBName != $categoryName) {
            return $app->redirect("/grammar-rule/show/{$categoryId}/{$categoryDBName}");
        }

        $categoryWithAncestors = $model->getCategoryAncestors($categoryId);

        return $app['twig']->render(
            'category/show.twig',
            array(
                'categories' => $categoryWithAncestors
            )
        );
    }

    public function newEdit(Request $request, Application $app, $categoryId=Null)
    {
        $categoryForm = new CategoryForm();

        if ($categoryForm->fillFromRequest($request)) {
            $rs = $categoryForm->write($app);
        }
        else {
            $rs = $categoryForm->read($app, $categoryId);
        }

        if ($rs['result'] == 'abort') {
            $app->abort(404);
        }
        else if ($rs['result'] == 'redirect') {
            return $app->redirect(sprintf('/category/edit/%s/', $rs['id']));
        }

        return $app['twig']->render(
            'category/new-edit.twig',
            array(
                'form' => $categoryForm
            )
        );
    }
}
