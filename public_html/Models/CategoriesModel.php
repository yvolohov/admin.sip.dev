<?php

namespace Sip\Models;

class CategoriesModel extends BaseModel
{
    public function __construct($db)
    {
        parent::__construct($db);
    }

    public function getCategoriesDictionary()
    {
        $treeData = $this->getDB()->fetchAll(
            'SELECT id, url_name, native_name, foreign_name, parent_id
            FROM categories
            ORDER BY parent_id, sort_field, id'
        );
        $treeDict = array();

        foreach ($treeData as $record) {
            $key = $record['parent_id'];

            if (array_key_exists($key, $treeDict)) {
                $treeDict[$key][] = $record;
            }
            else {
                $treeDict[$key] = array($record);
            }
        }
        return $treeDict;
    }

    public function getRootCategoryId()
    {
        $result = $this->getDB()->fetchColumn(
            'SELECT id FROM categories
            WHERE parent_id = 0 ORDER BY id LIMIT 0, 1'
        );
        return $result;
    }
}