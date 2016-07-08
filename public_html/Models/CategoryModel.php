<?php

namespace Sip\Models;

class CategoryModel extends BaseModel
{
    const NEW_CATEGORY = 0;
    const EXISTING_CATEGORY = 1;
    const WRONG_CATEGORY = -1;
    const NEW_DESCRIPTION = 0;
    const EXISTING_DESCRIPTION = 1;

    public function __construct($db)
    {
        parent::__construct($db);
    }

    public function setCategory($categoryForm)
    {
        $result = Null;
        $conn = $this->getDB();
        $conn->beginTransaction();

        try {
            $categoryState = $this->getCategoryState($categoryForm->getParam('id', 'value'));

            switch ($categoryState) {
                case self::NEW_CATEGORY:
                    $result = $this->insertCategory($categoryForm);
                    break;
                case self::EXISTING_CATEGORY:
                    $result = $this->updateCategory($categoryForm);
                    break;
                case self::WRONG_CATEGORY:
                    $categoryForm->addError('Bad category Id');
                    $conn->rollback();
                    return Null;
            }

            $descriptionState = $this->getDescriptionState($result);

            switch ($descriptionState) {
                case self::NEW_DESCRIPTION:
                    $this->insertDescription($result, $categoryForm);
                    break;
                case self::EXISTING_DESCRIPTION:
                    $this->updateDescription($result, $categoryForm);
                    break;
            }

            $conn->commit();
        }
        catch (\Exception $e) {
            $conn->rollback();
            $categoryForm->addError($e->getMessage());
            $result = Null;
        }

        return $result;
    }

    private function getCategoryState($categoryId)
    {
        if ($categoryId == '') {
            return self::NEW_CATEGORY;
        }

        $result = $this->getDB()->fetchColumn(
            'SELECT count(*) cnt FROM categories WHERE id = :id',
            array(
                'id' => $categoryId
            )
        );

        return ($result > 0) ? self::EXISTING_CATEGORY : self::WRONG_CATEGORY;
    }

    private function insertCategory($categoryForm)
    {
        $conn = $this->getDB();
        $conn->executeUpdate(
            'INSERT INTO categories (url_name, foreign_name,
            native_name, sort_field, parent_id) VALUES (:url_name,
            :foreign_name, :native_name, :sort_field, :parent_id)',
            array(
                'url_name' => $categoryForm->getParam('url_name', 'value'),
                'foreign_name' => $categoryForm->getParam('foreign_name', 'value'),
                'native_name' => $categoryForm->getParam('native_name', 'value'),
                'sort_field' => $categoryForm->getParam('sort_field', 'value'),
                'parent_id' => $categoryForm->getParam('parent_id', 'value')
            )
        );
        return $conn->lastInsertId();
    }

    private function updateCategory($categoryForm)
    {
        $id = $categoryForm->getParam('id', 'value');
        $this->getDB()->executeUpdate(
            'UPDATE categories SET url_name = :url_name, foreign_name = :foreign_name,
            native_name = :native_name, sort_field = :sort_field, parent_id = :parent_id WHERE id = :id',
            array(
                'url_name' => $categoryForm->getParam('url_name', 'value'),
                'foreign_name' => $categoryForm->getParam('foreign_name', 'value'),
                'native_name' => $categoryForm->getParam('native_name', 'value'),
                'sort_field' => $categoryForm->getParam('sort_field', 'value'),
                'parent_id' => $categoryForm->getParam('parent_id', 'value'),
                'id' => $id
            )
        );
        return $id;
    }

    private function getDescriptionState($categoryId)
    {
        $result = $this->getDB()->fetchColumn(
            'SELECT count(*) cnt FROM descriptions WHERE category_id = :category_id',
            array(
                'category_id' => $categoryId
            )
        );
        return ($result > 0) ? self::EXISTING_DESCRIPTION : self::NEW_DESCRIPTION;
    }

    private function insertDescription($categoryId, $categoryForm)
    {
        $this->getDB()->executeUpdate(
            'INSERT INTO descriptions (description, category_id)
            VALUES (:description, :category_id)',
            array(
                'description' => $categoryForm->getParam('description', 'value'),
                'category_id' => $categoryId
            )
        );
    }

    private function updateDescription($categoryId, $categoryForm)
    {
        $this->getDB()->executeUpdate(
            'UPDATE descriptions SET description = :description
            WHERE category_id = :category_id',
            array(
                'description' => $categoryForm->getParam('description', 'value'),
                'category_id' => $categoryId
            )
        );
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

    public function getQuestionsCountInCategory($categoryId)
    {
        return $this->getDB()->fetchColumn(
            'SELECT count(*) cnt FROM questions WHERE category_id = :category_id',
            array(
                'category_id' => $categoryId
            )
        );
    }
}