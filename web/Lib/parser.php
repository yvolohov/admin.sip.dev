<?php

namespace Sip\Lib;

function parseRequest($data, $prefix='')
{
    $result = array();
    $mainPattern = '/^' . $prefix . '\[[0-9]{1,255}\]\[[_A-Za-z]{1}[_A-Za-z0-9]{0,255}\]$/i';
    $searchPattern = '/\[[^\[\]]+\]/';

    foreach ($data as $key => $value) {

        if (preg_match($mainPattern, $key)) {

        }
    }

    //$result[] = array('foreign_template' => '#1{111|222|}', 'native_template' => '#1{111|222}');
    //$result[] = array('foreign_template' => '#1{111|222|}', 'native_template' => '#1{111|222}');
    return $result;
}