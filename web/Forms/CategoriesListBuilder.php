<?php

namespace Sip\Forms;

class CategoriesListBuilder
{
    private $categoriesModel;
    private $categoriesList;
    private $categoriesDict;
    private $exceptCategory;
    private $short;

    public function __construct($categoriesModel, $exceptCategory=Null, $short=False)
    {
        $this->categoriesModel = $categoriesModel;
        $this->categoriesList = array();
        $this->categoriesDict = array();
        $this->exceptCategory = $exceptCategory;
        $this->short = $short;
    }

    public function setExceptCategoryOption($exceptCategory)
    {
        $this->exceptCategory = $exceptCategory;
    }

    public function setShortOption($short)
    {
        $this->short = $short;
    }

    public function getList()
    {
        $this->categoriesList = (!$this->short) ? array(0 => 'No category') : array(0);
        $this->categoriesDict = $this->categoriesModel->getCategoriesDictionary();

        if (count($this->categoriesDict) == 0) {
            return $this->categoriesList;
        }

        $keys = array_keys($this->categoriesDict);
        $categoryKey = min($keys);
        $this->makeList($categoryKey, 1);

        return $this->categoriesList;
    }

    private function makeList($categoryKey, $level)
    {
        $categories = $this->categoriesDict[$categoryKey];

        foreach ($categories as $category) {

            if ($category['id'] == $this->exceptCategory) {
                continue;
            }

            $isGroup = array_key_exists($category['id'], $this->categoriesDict);

            if (!$this->short) {
                $lines = str_repeat('- ', $level);
                $this->categoriesList[$category['id']] = $lines . $category['foreign_name'];
            }
            else {
                $this->categoriesList[] = $category['id'];
            }

            if ($isGroup) {
                $this->makeList($category['id'], $level + 1);
            }
        }
    }
}