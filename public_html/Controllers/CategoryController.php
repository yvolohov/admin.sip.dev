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
        $categoryModel = new CategoryModel($app['db']);
        $category = $categoryModel->getCategoryById($categoryId);

        if ($category == Null) {
            $app->abort(404);
        }

        $categoryDBName = $category['url_name'];

        if ($categoryDBName != $categoryName) {
            return $app->redirect("/grammar-rule/show/{$categoryId}/{$categoryDBName}");
        }

        $categoryWithAncestors = $categoryModel->getCategoryAncestors($categoryId);
        $categoryHasQuestions = $categoryModel->getQuestionsCountInCategory($categoryId) > 0;

        return $app['twig']->render(
            'category/show.twig',
            array(
                'categories' => $categoryWithAncestors,
                'startUrl' => "/test/start/{$categoryId}/",
                'completeUrl' => "/test/complete/{$categoryId}/",
                'categoryHasQuestions' => $categoryHasQuestions
            )
        );
    }

    public function newEdit(Request $request, Application $app, $categoryId=Null)
    {
        $categoryForm = new CategoryForm();
        $formData = $request->request->get($categoryForm->getFormName());

        if ($formData == Null) {
            $rs = $categoryForm->read($app, $categoryId);
        }
        else {
            $rs = $categoryForm->write($app, $formData);
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
