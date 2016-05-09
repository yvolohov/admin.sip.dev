<?php

namespace Sip\Forms;

use Sip\Models\CategoriesModel;
use Sip\Models\CategoryModel;

class CategoryForm extends BaseForm
{
    public function __construct()
    {
        parent::__construct('category');
        $this->setField('id', 'hidden', '');
        $this->setField('url_name', 'text', '');
        $this->setField('foreign_name', 'text', '');
        $this->setField('native_name', 'text', '');
        $this->setField('sort_field', 'text', 0);
        $this->setField('description', 'textarea', '');
        $this->setSelectField('parent_id', 0);
    }

    public function read($app, $categoryId)
    {
        $categoriesModel = new CategoriesModel($app['db']);
        $categoriesListBuilder = new CategoriesListBuilder($categoriesModel);

        if ($categoryId == Null) {
            $this->setParam('parent_id', 'select_list', $categoriesListBuilder->getList());
            return array('result' => 'show');
        }

        $categoryModel = new CategoryModel($app['db']);
        $category = $categoryModel->getCategoryById($categoryId);

        if ($category == Null) {
            return array('result' => 'abort');
        }

        $this->fillFromDB($category);
        $categoriesListBuilder->setExceptCategoryOption($categoryId);
        $this->setParam('parent_id', 'select_list', $categoriesListBuilder->getList());
        return array('result' => 'show');
    }
}