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
            'INSERT INTO questions (foreign_sentence, native_sentence,
            templates_cnt, sentences_cnt, category_id, created, updated)
            VALUES (:foreign_sentence, :native_sentence, :templates_cnt,
            :sentences_cnt, :category_id, NOW(), NOW())',
            array(
                'foreign_sentence' => $questionForm->getParam('foreign_sentence', 'value'),
                'native_sentence' => $questionForm->getParam('native_sentence', 'value'),
                'templates_cnt' => $questionForm->getListFieldLength('templates_list'),
                'sentences_cnt' => $questionForm->getListFieldLength('sentences_list'),
                'category_id' => $questionForm->getParam('category_id', 'value')
            )
        );
        return $conn->lastInsertId();
    }

    private function updateQuestion($questionForm)
    {
        $id = $questionForm->getParam('id', 'value');
        $this->getDB()->executeUpdate(
            'UPDATE questions SET foreign_sentence = :foreign_sentence,
            native_sentence = :native_sentence, templates_cnt = :templates_cnt,
            sentences_cnt = :sentences_cnt, category_id = :category_id,
            updated = NOW() WHERE id = :id',
            array(
                'foreign_sentence' => $questionForm->getParam('foreign_sentence', 'value'),
                'native_sentence' => $questionForm->getParam('native_sentence', 'value'),
                'templates_cnt' => $questionForm->getListFieldLength('templates_list'),
                'sentences_cnt' => $questionForm->getListFieldLength('sentences_list'),
                'category_id' => $questionForm->getParam('category_id', 'value'),
                'id' => $id
            )
        );
        return $id;
    }
}