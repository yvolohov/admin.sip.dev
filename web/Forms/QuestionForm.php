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
        $this->setListField('templates_list');
        $this->setListField('sentences_list');
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
        $templates = $this->readTemplates($questionModel, $questionId);
        $sentences = $this->readSentences($questionModel, $questionId);
        $this->setParam('templates_list', 'value', $templates);
        $this->setParam('sentences_list', 'value', $sentences);
        return array('result' => 'show');
    }

    private function readTemplates($questionModel, $questionId)
    {
        $templates = $questionModel->getTemplatesByQuestionId($questionId);
        $templateForms = array();

        foreach ($templates as $template) {
            $templateForm = new TemplateForm();
            $templateForm->fillFromDB($template);
            $templateForms[] = $templateForm;
        }
        return $templateForms;
    }

    private function readSentences($questionModel, $questionId)
    {
        $sentences = $questionModel->getSentencesByQuestionId($questionId);
        $sentenceForms = array();

        foreach ($sentences as $sentence) {
            $sentenceForm = new SentenceForm();
            $sentenceForm->fillFromDB($sentence);
            $sentenceForms[] = $sentenceForm;
        }
        return $sentenceForms;
    }
}