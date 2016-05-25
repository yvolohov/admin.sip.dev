<?php

namespace Sip\Lib;

class Paginator
{
    private $tableRecords = 0;
    private $pageRecords = 20;
    private $paginatorLength = 9;
    private $currentPage = 1;

    public function __construct()
    {
        //
    }

    public function setTableRecords($tableRecords)
    {
        $this->tableRecords = $tableRecords;
    }

    public function setPageRecords($pageRecords)
    {
        $this->pageRecords = $pageRecords;
    }

    public function setPaginatorLength($paginatorLength)
    {
        $this->paginatorLength = $paginatorLength;
    }

    public function setCurrentPage($currentPage)
    {
        $this->currentPage = $currentPage;
    }

    public function getPaginatorParams()
    {
        $pagParams = $this->getEmptyParams();

        if ($this->currentPage > 0) {
            $pagParams['limitOffset'] = ($this->currentPage - 1) * $this->pageRecords;
            $pagParams['limitRows'] = $this->pageRecords;
        }

        $pagesCount = $this->getPaginatorPagesCount();

        if ($this->currentPage < 1 || $this->currentPage > $pagesCount
            || ($this->currentPage == 1 && $pagesCount == 1)) {
            return $pagParams;
        }

        array_push($pagParams['pages'], $this->currentPage);
        $pagParams['currentPage'] = $this->currentPage;
        $maxPages = min($this->paginatorLength, $pagesCount);
        $minPage = $maxPage = $this->currentPage;

        while (count($pagParams['pages']) < $maxPages) {
            $minPage--;
            $maxPage++;

            if ($minPage > 0) {
                array_push($pagParams['pages'], $minPage);
            }

            if ($maxPage <= $pagesCount) {
                array_push($pagParams['pages'], $maxPage);
            }
        }

        sort($pagParams['pages']);
        $pagParams['prevPage'] = ($this->currentPage > 1) ? $this->currentPage - 1 : $pagParams['prevPage'];
        $pagParams['nextPage'] = ($this->currentPage < $pagesCount) ? $this->currentPage + 1 : $pagParams['nextPage'];

        return $pagParams;
    }

    public function getReversePaginatorParams()
    {
        $reverseParams = $this->getPaginatorParams();
        rsort($reverseParams['pages']);
        $change = $reverseParams['prevPage'];
        $reverseParams['prevPage'] = $reverseParams['nextPage'];
        $reverseParams['nextPage'] = $change;

        return $reverseParams;
    }

    public function getPaginatorPagesCount()
    {
        return ($this->tableRecords > 0) ? (int)(ceil($this->tableRecords / (float)($this->pageRecords))) : 1;
    }

    private function getEmptyParams()
    {
        return array(
            'pages' => array(),
            'currentPage' => Null,
            'prevPage' => Null,
            'nextPage' => Null,
            'limitOffset' => 0,
            'limitRows' => 0
        );
    }
}