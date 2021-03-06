<?php

namespace Sip\Forms;

use Sip\Models\CategoriesModel;
use Sip\Models\CategoryModel;
use Symfony\Component\Validator\Constraints as Assert;

class CategoryForm extends BaseForm
{
    public function __construct()
    {
        parent::__construct('category');
        $this->setField('id', 'Id', 'hidden', '', array(
            new Assert\Regex(array('pattern' => '/^(|\d+)$/'))
        ));
        $this->setField('url_name', 'Имя для URL', 'text', '', array(
            new Assert\NotBlank(),
            new Assert\Regex(array('pattern' => '/^(\w|-)+$/'))
        ));
        $this->setField('foreign_name', 'Имя на изучаемом языке', 'text', '', array(
            new Assert\NotBlank()
        ));
        $this->setField('native_name', 'Имя на базовом языке', 'text', '', array(
            new Assert\NotBlank()
        ));
        $this->setField('sort_field', 'Поле сортировки', 'text', 0, array(
            new Assert\Type(array('type' => 'numeric')),
            new Assert\GreaterThanOrEqual(array('value' => 0))
        ));
        $this->setField('description', 'Описание', 'textarea', '');
        $this->setSelectField('parent_id', 'Родительская категория', 0, array(), array(
            new Assert\Type(array('type' => 'numeric')),
            new Assert\GreaterThanOrEqual(array('value' => 0))
        ));
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

        $this->fillForm($category);
        $categoriesListBuilder->setExceptCategoryOption($categoryId);
        $this->setParam('parent_id', 'select_list', $categoriesListBuilder->getList());
        return array('result' => 'show');
    }

    public function write($app, $formData)
    {
        $this->fillForm($formData);
        $categoriesModel = new CategoriesModel($app['db']);
        $categoriesListBuilder = new CategoriesListBuilder($categoriesModel);

        /* Формируем список выбора родительской категории,
         * в списке не должно быть текущей категории и ее потомков */
        $currentCategory = $this->getParam('id', 'value');
        $categoriesListBuilder->setExceptCategoryOption($currentCategory);
        $categoriesList = $categoriesListBuilder->getList();
        $this->setParam('parent_id', 'select_list', $categoriesList);

        if (!$this->validate($app)) {
            return array('result' => 'show');
        }

        /* Перед записью проверяем, входит ли выбранная
         * родительская категория в список разрешенных для выбора */
        $parentCategory = $this->getParam('parent_id', 'value');

        if (!array_key_exists($parentCategory, $categoriesList)) {
            $this->addError('Parent category: This value is not valid.');
            return array('result' => 'show');
        }

        $categoryModel = new CategoryModel($app['db']);
        $categoryId = $categoryModel->setCategory($this);

        if ($categoryId == Null) {
            return array('result' => 'show');
        }

        return array('result' => 'redirect', 'id' => $categoryId);
    }
}