<?php

namespace Sip\Models;

use Sip\Lib;

class QuestionsModel extends BaseModel
{
    public function __construct($db)
    {
        parent::__construct($db);
    }

    public function getQuestionsByCategory($categoryId)
    {
        $result = $this->getDB()->fetchAll(
            'SELECT * FROM questions
            WHERE category_id
            IN (SELECT id FROM categories WHERE id = :id)
            ORDER BY id',
            array(
                'id' => $categoryId
            )
        );
        return $result;
    }

    public function getQuestionsPaginator($pageId)
    {
        $questionsCount = $this->getQuestionsCount();
        $paginator = new Lib\Paginator();
        $paginator->setTableRecords($questionsCount);
        $paginator->setPageRecords(10);
        $pageId = ($pageId == Null) ? $paginator->getPaginatorPagesCount() : $pageId;
        $paginator->setCurrentPage($pageId);
        return $paginator->getReversePaginatorParams();
    }

    public function getQuestions($offset, $limit)
    {
        $db = $this->getDB();
        $stm = $db->prepare(
            'SELECT que.id, que.keywords, que.foreign_sentence,
            que.native_sentence, que.templates_cnt,
            que.sentences_cnt, IFNULL(cat.foreign_name, "") category
            FROM questions que
            LEFT JOIN categories cat
            ON (que.category_id = cat.id)
            ORDER BY que.id LIMIT :offset, :limit'
        );
        $stm->bindValue('offset', $offset, \PDO::PARAM_INT);
        $stm->bindValue('limit', $limit, \PDO::PARAM_INT);
        $stm->execute();
        return $stm->fetchAll();
    }

    private function getQuestionsCount()
    {
        return $this->getDB()->fetchColumn('SELECT count(*) cnt FROM questions');
    }
}