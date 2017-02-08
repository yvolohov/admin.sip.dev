<?php

namespace Sip\Forms;

use Sip\Models\CategoriesModel;
use Sip\Models\QuestionModel;
use Symfony\Component\Validator\Constraints as Assert;

class QuestionForm extends BaseForm
{
    const TEMPLATES_REQUEST_PREFIX = 'templates';
    const SENTENCES_REQUEST_PREFIX = 'sentences';

    public function __construct()
    {
        parent::__construct('question');
        $this->setField('id', 'Id', 'hidden', '', array(
            new Assert\Regex(array('pattern' => '/^(|\d+)$/'))
        ));
        $this->setSelectField('category_id', 'Категория', 0, array(), array(
            new Assert\Type(array('type' => 'numeric')),
            new Assert\GreaterThanOrEqual(array('value' => 1))
        ));
        $this->setField('keywords', 'Ключи', 'text', '');
        $this->setField('foreign_sentence', 'Пример на изучаемом языке', 'text', '');
        $this->setField('native_sentence', 'Пример на базовом языке', 'text', '');
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

        $this->fillForm($question);
        $templates = $this->readTemplates($questionModel, $questionId);
        $sentences = $this->readSentences($questionModel, $questionId);
        $this->setParam('templates_list', 'value', $templates);
        $this->setParam('sentences_list', 'value', $sentences);
        return array('result' => 'show');
    }

    public function write($app, $request, $formData)
    {
        $this->fillForm($formData);
        $categoriesModel = new CategoriesModel($app['db']);
        $categoriesListBuilder = new CategoriesListBuilder($categoriesModel);
        $this->setParam('category_id', 'select_list', $categoriesListBuilder->getList());

        $templatesData = $request->request->get(self::TEMPLATES_REQUEST_PREFIX);
        $sentencesData = $request->request->get(self::SENTENCES_REQUEST_PREFIX);

        if (is_array($templatesData)) {
            $this->writeTemplates($templatesData);
        }

        if (is_array($sentencesData)) {
            $this->writeSentences($sentencesData);
        }

        if (!$this->validate($app)) {
            return array('result' => 'show');
        }

        $questionModel = new QuestionModel($app['db']);
        $questionId = $questionModel->setQuestion($this);

        if ($questionId == Null) {
            return array('result' => 'show');
        }

        return array('result' => 'redirect', 'id' => $questionId);
    }

    private function readTemplates($questionModel, $questionId)
    {
        $templates = $questionModel->getTemplatesByQuestionId($questionId);
        $templateForms = array();

        foreach ($templates as $template) {
            $templateForm = new TemplateForm();
            $templateForm->fillForm($template);
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
            $sentenceForm->fillForm($sentence);
            $sentenceForms[] = $sentenceForm;
        }
        return $sentenceForms;
    }

    private function writeTemplates($templates)
    {
        $templatesList = array();

        foreach ($templates as $template) {
            $templateForm = new TemplateForm();
            $templateForm->fillForm($template);
            $templatesList[] = $templateForm;
        }
        $this->setParam('templates_list', 'value', $templatesList);
    }

    private function writeSentences($sentences)
    {
        $sentencesList = array();

        foreach ($sentences as $sentence) {
            $sentenceForm = new SentenceForm();
            $sentenceForm->fillForm($sentence);
            $sentencesList[] = $sentenceForm;
        }
        $this->setParam('sentences_list', 'value', $sentencesList);
    }
}