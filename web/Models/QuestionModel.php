<?php

namespace Sip\Models;

class QuestionModel extends BaseModel
{
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
}