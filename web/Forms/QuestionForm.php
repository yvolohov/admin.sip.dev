<?php

namespace Sip\Forms;

use Sip\Models\CategoriesModel;
use Sip\Models\QuestionModel;
use Symfony\Component\Validator\Constraints as Assert;

class QuestionForm extends BaseForm
{
    public function __construct()
    {
        parent::__construct('question');
        $this->setField('id', 'Question Id', 'hidden', array(
            new Assert\Regex(array('pattern' => '/^(|\d+)$/'))
        ));
        $this->setSelectField('category_id', 'Category', 0, array(), array(
            new Assert\Type(array('type' => 'numeric')),
            new Assert\GreaterThanOrEqual(array('value' => 1))
        ));
        $this->setField('foreign_sentence', 'Foreign sentence', 'text', '');
        $this->setField('native_sentence', 'Native sentence', 'text', '');
    }

    public function read($app, $questionId)
    {
        $categoriesModel = new CategoriesModel($app['db']);
        $categoriesListBuilder = new CategoriesListBuilder($categoriesModel);
        $this->setParam('category_id', 'select_list', $categoriesListBuilder->getList());

        if ($questionId == Null) {
            return array('result' => 'show');
        }

        $questionModel = new QuestionModel($app['db']);
        $question = $questionModel->getQuestionById($questionId);

        if ($question == Null) {
            return array('result' => 'abort');
        }

        $this->fillFromDB($question);
        return array('result' => 'show');
    }
}