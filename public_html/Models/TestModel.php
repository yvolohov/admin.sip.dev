<?php

namespace Sip\Models;

class TestModel extends BaseModel
{
    private $session;

    public function __construct($db, $session)
    {
        parent::__construct($db);
        $this->session = $session;
    }

    /* standard test */
    public function startTest($questionsCount=25, $sentencesCount=2)
    {
        $userId = $this->getUserId();
        $returnStructure = array();

        if ($userId == Null) {
            $returnStructure['success'] = False;
            $returnStructure['error_description'] = 'User Id is not defined';
            return $returnStructure;
        }
        $conn = $this->getDB();
        $conn->beginTransaction();

        try {
            $this->uncheckTestRecords($userId);
            $this->addTestRecords($userId);
            $questions =& $this->getQuestions($userId, $questionsCount, $sentencesCount);
            $this->checkTestRecords($userId, $questions);
            $sentences = $this->getSentences($questions);
            $conn->commit();
            $returnStructure['success'] = True;
            $returnStructure['data'] = $sentences;
        }
        catch (\Exception $e) {
            $conn->rollback();
            $returnStructure['success'] = False;
            $returnStructure['error_description'] = $e->getMessage();
        }

        return $returnStructure;
    }

    public function completeTest()
    {
        $userId = $this->getUserId();
        $returnStructure = array();

        if ($userId == Null) {
            $returnStructure['success'] = False;
            $returnStructure['error_description'] = 'User Id is not defined';
            return $returnStructure;
        }
        $conn = $this->getDB();
        $conn->beginTransaction();

        try {
            $this->increaseCounters($userId);
            $conn->commit();
            $returnStructure['success'] = True;
        }
        catch (\Exception $e) {
            $conn->rollback();
            $returnStructure['success'] = False;
            $returnStructure['error_description'] = $e->getMessage();
        }

        return $returnStructure;
    }

    /* test by category */
    public function startTestByCategory($categoryId, $sentencesCount=2)
    {
        $userId = $this->getUserId();
        $returnStructure = array();

        if ($userId == Null) {
            $returnStructure['success'] = False;
            $returnStructure['error_description'] = 'User Id is not defined';
            return $returnStructure;
        }
        $conn = $this->getDB();
        $conn->beginTransaction();

        try {
            $questions =& $this->getQuestionsByCategory($categoryId, $sentencesCount);
            $sentences = $this->getSentences($questions);
            $conn->commit();
            $returnStructure['success'] = True;
            $returnStructure['data'] = $sentences;
        }
        catch (\Exception $e) {
            $conn->rollback();
            $returnStructure['success'] = False;
            $returnStructure['error_description'] = $e->getMessage();
        }

        return $returnStructure;
    }

    public function completeTestByCategory($categoryId)
    {
        return array('category_id' => $categoryId);
    }

    /* standard test */
    private function getUserId()
    {
        $user = $this->session->get('user');

        if (!is_array($user)) {
            return Null;
        }

        return (isset($user['id'])) ? $user['id'] : Null;
    }

    private function uncheckTestRecords($userId)
    {
        $this->getDB()->executeUpdate(
            'UPDATE tests SET is_selected = 0
            WHERE user_id = :user_id AND is_selected > 0',
            array(
                'user_id' => $userId
            )
        );
    }

    private function addTestRecords($userId)
    {
        $testsTable = $this->getDB()->fetchAll(
            'SELECT que.id question_id FROM questions que
            LEFT JOIN (SELECT question_id FROM tests WHERE user_id = :user_id) tst
            ON (que.id = tst.question_id)
            WHERE tst.question_id IS NULL',
            array(
                'user_id' => $userId
            )
        );

        if (count($testsTable) == 0) {
            return;
        }

        $requestText =
            'INSERT INTO tests (user_id, question_id, is_selected,
            passages_cnt, first_passage, last_passage) VALUES ';
        $requestRow =
            '(%d, %d, %d, %d, "0000-00-00 00:00:00", "0000-00-00 00:00:00")';
        $needComma = False;

        foreach ($testsTable as $row) {
            $requestText .= ($needComma) ? ', ' : '';
            $requestText .= sprintf($requestRow, $userId, $row['question_id'], 0, 0);
            $needComma = True;
        }
        $this->getDB()->executeUpdate($requestText);
    }

    private function &getQuestions($userId, $questionsCount, $sentencesCount)
    {
        $conn = $this->getDB();
        $stm = $conn->prepare(
            'SELECT que.id question_id, que.sentences_cnt FROM questions que
            LEFT JOIN (SELECT question_id, passages_cnt, last_passage FROM tests WHERE user_id = :user_id) tst
            ON (que.id = tst.question_id) ORDER BY last_passage, id
            LIMIT :questions_count'
        );
        
        $stm->bindValue('user_id', $userId, \PDO::PARAM_INT);
        $stm->bindValue('questions_count', $questionsCount, \PDO::PARAM_INT);
        $stm->execute();
        $questions = $stm->fetchAll();
        $this->selectSentences($questions, $sentencesCount);

        return $questions;
    }

    private function selectSentences(&$questions, $sentencesCount)
    {
        $questionsCount = count($questions);

        for ($queIdx = 0; $queIdx < $questionsCount; $queIdx++) {
            $questions[$queIdx]['selected_sentences'] = array();
            $sentencesExist = $questions[$queIdx]['sentences_cnt'];
            $sentencesNeed = min($sentencesCount, $sentencesExist);
            $pool = range(1, $sentencesExist);

            for ($sentIdx = 0; $sentIdx < $sentencesNeed; $sentIdx++) {
                $poolIdx = mt_rand(0, count($pool) - 1);
                $questions[$queIdx]['selected_sentences'][] = $pool[$poolIdx];
                array_splice($pool, $poolIdx, 1);
            }

            sort($questions[$queIdx]['selected_sentences']);
        }
    }

    private function checkTestRecords($userId, &$questions)
    {
        $requestText =
            'UPDATE tests SET is_selected = 1
            WHERE user_id = :user_id AND question_id = :question_id';

        foreach ($questions as $question) {
            
            if (count($question['selected_sentences']) == 0) {
                continue;
            }

            $this->getDB()->executeUpdate(
                $requestText,
                array(
                    'user_id' => $userId,
                    'question_id' => $question['question_id']
                )
            );
        }
    }

    private function getSentences(&$questions)
    {
        if (count($questions) == 0) {
            return array();
        }

        $needUnion = False;
        $subQuery = '';
        $subSubQuery =
            '(SELECT question_id, id, foreign_sentence, native_sentence, parts
            FROM sentences WHERE question_id = %d AND id = %d)';
        $query =
            'SELECT foreign_sentence, native_sentence, parts FROM (%s) sentences
            ORDER BY question_id, id';

        foreach ($questions as $question) {

            foreach ($question['selected_sentences'] as $sentenceId) {
                $subQuery .= ($needUnion) ? ' UNION ' : '';
                $subQuery .= sprintf($subSubQuery, $question['question_id'], $sentenceId);
                $needUnion = True;
            }
        }

        return $this->getDB()->fetchAll(sprintf($query, $subQuery));
    }

    private function increaseCounters($userId)
    {
        $this->getDB()->executeUpdate(
            'UPDATE tests SET
            passages_cnt = passages_cnt + 1,
            is_selected = 0,
            first_passage = IF(first_passage = "0000-00-00 00:00:00", NOW(), first_passage),
            last_passage = NOW()
            WHERE user_id = :user_id AND is_selected > 0',
            array(
                'user_id' => $userId
            )
        );
    }

    /* test by category */
    private function &getQuestionsByCategory($categoryId, $sentencesCount)
    {
        $questions = $this->getDB()->fetchAll(
            'SELECT id question_id, sentences_cnt FROM questions que
            WHERE category_id = :category_id',
            array(
                'category_id' => $categoryId
            )
        );
        $this->selectSentences($questions, $sentencesCount);

        return $questions;
    }
}