<?php

namespace Sip\Models;

class QuestionModel extends BaseModel
{
    const NEW_QUESTION = 0;
    const EXISTING_QUESTION = 1;
    const WRONG_QUESTION = -1;

    public function __construct($db)
    {
        parent::__construct($db);
    }

    public function getQuestionById($questionId)
    {
        $result = $this->getDB()->fetchAssoc(
            'SELECT * FROM questions WHERE id = :id',
            array(
                'id' => $questionId
            )
        );
        return $result;
    }

    public function getTemplatesByQuestionId($questionId)
    {
        return $this->getDB()->fetchAll(
            'SELECT * FROM templates
            WHERE question_id = :question_id ORDER BY id',
            array(
                'question_id' => $questionId
            )
        );
    }

    public function getSentencesByQuestionId($questionId)
    {
        return $this->getDB()->fetchAll(
            'SELECT * FROM sentences
            WHERE question_id = :question_id ORDER BY id',
            array(
                'question_id' => $questionId
            )
        );
    }

    public function setQuestion($questionForm)
    {
        $result = Null;
        $conn = $this->getDB();
        $conn->beginTransaction();

        try {
            $questionState = $this->getQuestionState($questionForm->getParam('id', 'value'));

            switch ($questionState) {
                case self::NEW_QUESTION:
                    $result = $this->insertQuestion($questionForm);
                    break;
                case self::EXISTING_QUESTION:
                    $result = $this->updateQuestion($questionForm);
                    break;
                case self::WRONG_QUESTION:
                    $questionForm->addError('Bad question Id');
                    $conn->rollback();
                    return Null;
            }

            $this->rewriteTemplates($result, $questionForm);
            $this->rewriteSentences($result, $questionForm);
            $conn->commit();
        }
        catch (\Exception $e) {
            $conn->rollback();
            $result = Null;
        }

        return $result;
    }

    private function getQuestionState($questionId)
    {
        if ($questionId == '') {
            return self::NEW_QUESTION;
        }

        $result = $this->getDB()->fetchColumn(
            'SELECT count(*) cnt FROM questions WHERE id = :id',
            array(
                'id' => $questionId
            )
        );

        return ($result > 0) ? self::EXISTING_QUESTION : self::WRONG_QUESTION;
    }

    private function insertQuestion($questionForm)
    {
        $conn = $this->getDB();
        $conn->executeUpdate(
            'INSERT INTO questions (keywords, foreign_sentence, native_sentence,
            source, templates_cnt, sentences_cnt, category_id, created, updated)
            VALUES (:keywords, :foreign_sentence, :native_sentence, :source, :templates_cnt,
            :sentences_cnt, :category_id, NOW(), NOW())',
            array(
                'keywords' => $questionForm->getParam('keywords', 'value'),
                'foreign_sentence' => $questionForm->getParam('foreign_sentence', 'value'),
                'native_sentence' => $questionForm->getParam('native_sentence', 'value'),
                'source' => $questionForm->getParam('source', 'value'),
                'templates_cnt' => count($questionForm->getParam('templates_list', 'value')),
                'sentences_cnt' => count($questionForm->getParam('sentences_list', 'value')),
                'category_id' => $questionForm->getParam('category_id', 'value')
            )
        );
        return $conn->lastInsertId();
    }

    private function updateQuestion($questionForm)
    {
        $id = $questionForm->getParam('id', 'value');
        $this->getDB()->executeUpdate(
            'UPDATE questions SET keywords = :keywords, foreign_sentence = :foreign_sentence,
            native_sentence = :native_sentence, templates_cnt = :templates_cnt, source = :source,
            sentences_cnt = :sentences_cnt, category_id = :category_id,
            updated = NOW() WHERE id = :id',
            array(
                'keywords' => $questionForm->getParam('keywords', 'value'),
                'foreign_sentence' => $questionForm->getParam('foreign_sentence', 'value'),
                'native_sentence' => $questionForm->getParam('native_sentence', 'value'),
                'source' => $questionForm->getParam('source', 'value'),
                'templates_cnt' => count($questionForm->getParam('templates_list', 'value')),
                'sentences_cnt' => count($questionForm->getParam('sentences_list', 'value')),
                'category_id' => $questionForm->getParam('category_id', 'value'),
                'id' => $id
            )
        );
        return $id;
    }

    private function rewriteTemplates($questionId, $questionForm)
    {
        $conn = $this->getDB();
        $conn->executeUpdate(
            'DELETE FROM templates WHERE question_id = :question_id',
            array(
                'question_id' => $questionId
            )
        );

        $templatesList = $questionForm->getParam('templates_list', 'value');

        foreach ($templatesList as $key => $templateForm) {
            $conn->executeUpdate(
                'INSERT INTO templates (id, question_id, native_template, foreign_template)
                VALUES (:id, :question_id, :native_template, :foreign_template)',
                array(
                    'id' => $key + 1,
                    'question_id' => $questionId,
                    'native_template' => $templateForm->getParam('native_template', 'value'),
                    'foreign_template' => $templateForm->getParam('foreign_template', 'value')
                )
            );
        }
    }

    private function rewriteSentences($questionId, $questionForm)
    {
        $conn = $this->getDB();
        $conn->executeUpdate(
            'DELETE FROM sentences WHERE question_id = :question_id',
            array(
                'question_id' => $questionId
            )
        );

        $sentencesList = $questionForm->getParam('sentences_list', 'value');

        foreach ($sentencesList as $key => $sentenceForm) {
            $conn->executeUpdate(
                'INSERT INTO sentences (id, question_id, native_sentence, foreign_sentence, parts)
                VALUES (:id, :question_id, :native_sentence, :foreign_sentence, :parts)',
                array(
                    'id' => $key + 1,
                    'question_id' => $questionId,
                    'native_sentence' => $sentenceForm->getParam('native_sentence', 'value'),
                    'foreign_sentence' => $sentenceForm->getParam('foreign_sentence', 'value'),
                    'parts' => $sentenceForm->getParam('parts', 'value')
                )
            );
        }
    }
}