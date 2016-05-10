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
}