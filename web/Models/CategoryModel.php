<?php

namespace Sip\Models;

class CategoryModel extends BaseModel
{
    public function __construct($db)
    {
        parent::__construct($db);
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