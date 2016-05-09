<?php

namespace Sip\Models;

class CategoryModel extends BaseModel
{
    public function __construct($db)
    {
        parent::__construct($db);
    }

    public function setCategory($categoryForm)
    {
        $returnStructure = array();
        $conn = $this->getDB();
        $conn->beginTransaction();

        try {
            $categoryState = $this->getCategoryState($categoryForm->getParam('id', 'value'));

            switch ($categoryState) {
                case 0:
                    break;
                case 1:
                    break;
                case -1;
                    throw new \Exception('Bad category ID');
                    break;
            }

            $conn->commit();
            $returnStructure['success'] = True;
        }
        catch (\Exception $e) {
            $conn->rollback();
            $returnStructure['success'] = False;
        }

        return $returnStructure;
    }

    private function getCategoryState($categoryId)
    {
        if ($categoryId == '') {
            return 0;
        }

        $result = $this->getDB()->fetchColumn(
            'SELECT count(*) cnt FROM categories WHERE id = :id',
            array(
                'id' => $categoryId
            )
        );

        return ($result > 0) ? 1 : -1;
    }

    public function getCategoryById($categoryId)
    {
        $result = $this->getDB()->fetchAssoc(
            'SELECT cat.id, cat.url_name, cat.native_name, cat.foreign_name,
            cat.sort_field, cat.parent_id, IFNULL(des.description, "") description
            FROM categories cat
            LEFT JOIN descriptions des
            ON (cat.id = des.category_id)
            WHERE cat.id = :id',
            array(
                'id' => $categoryId
            )
        );
        return $result;
    }

    public function getCategoryAncestors($categoryId)
    {
        $indexes = $this->makeAncestorsList($categoryId);
        $needUnion = False;
        $query = '';
        $subQuery =
            '(SELECT cat.id, cat.url_name, cat.native_name
            FROM categories cat WHERE cat.id = %d)';

        foreach ($indexes as $index) {
            $query .= ($needUnion) ? ' UNION ' : '';
            $query .= sprintf($subQuery, $index);
            $needUnion = True;
        }

        $branch = $this->getDB()->fetchAll(
            sprintf('SELECT cat.id, cat.url_name, cat.native_name,
            IFNULL(des.description, "") description
            FROM (%s) cat
            LEFT JOIN descriptions des
            ON (cat.id = des.category_id)', $query)
        );
        return $branch;
    }

    private function makeAncestorsList($categoryId)
    {
        $rows = $this->getDB()->fetchAll('SELECT id, parent_id FROM categories');
        $branchDict = array();
        $branch = array();

        foreach ($rows as $row) {
            $branchDict[$row['id']] = $row['parent_id'];
        }

        while (True) {

            if (!array_key_exists($categoryId, $branchDict)) {
                break;
            }

            $branch[] = $categoryId;
            $categoryId = $branchDict[$categoryId];
        }

        if (count($branch) == 0) {
            $branch[] = $categoryId;
        }
        return array_reverse($branch);
    }
}