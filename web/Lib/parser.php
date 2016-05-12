<?php

namespace Sip\Lib;

function parseRequest($data, $prefix='')
{
    $result = array();
    $mainPattern = '/^' . $prefix . '\[[0-9]{1,255}\]\[[_A-Za-z]{1}[_A-Za-z0-9]{0,255}\]$/i';
    $searchPattern = '';

    $result[] = array('foreign_template' => '111', 'native_template' => '222');
    return $result;
}