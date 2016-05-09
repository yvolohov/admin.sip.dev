<?php

namespace Sip\Lib;

class SentencesBuilder
{
    const ENCODING = 'utf-8';

    public static function &getSentencesTable($pTemplate1, $pTemplate2)
    {
        $returnStruct = array('success' => true);
        $result1 = self::verifyTemplate($pTemplate1);
        $result2 = self::verifyTemplate($pTemplate2);

        if (!($result1 && $result2)) {
            self::addErrorDescription($returnStruct, 0, $result1, $result2);
            return $returnStruct;
        }

        $templateParts1 =& self::disassembleTemplate($pTemplate1);
        $templateParts2 =& self::disassembleTemplate($pTemplate2);
        $outerVariators =& self::getOuterVariators($templateParts1, $templateParts2);

        if ($outerVariators === null) {
            self::addErrorDescription($returnStruct, 1, false, false);
            return $returnStruct;
        }

        $result3 = self::verifyInnerVariators($outerVariators, $templateParts1);
        $result4 = self::verifyInnerVariators($outerVariators, $templateParts2);

        if (!($result3 && $result4)) {
            self::addErrorDescription($returnStruct, 2, $result3, $result4);
            return $returnStruct;
        }

        $returnStruct['data'] =& self::createSentencesTable($outerVariators,
            $templateParts1, $templateParts2);

        return $returnStruct;
    }

    public static function &getSentencesTableWithTokens($pTemplate1, $pTemplate2)
    {
        $returnStruct =& self::getSentencesTable($pTemplate1, $pTemplate2);

        if ($returnStruct['success']) {
            $countRows = count($returnStruct['data']);
            $table =& $returnStruct['data'];

            for ($index = 0; $index < $countRows; $index++) {
                $table[$index]['parts'] =
                    Tokenizer::toString($table[$index]['sentence1']);
            }
        }
        return $returnStruct;
    }

    private static function verifyTemplate($pTemplate)
    {
        $keySymbols = '#{|}';
        $templateLength = mb_strlen($pTemplate, self::ENCODING);
        $stack = array();
        
        for ($index = 0; $index < $templateLength; $index++) {
            $currentSymbol = mb_substr($pTemplate, $index, 1, self::ENCODING);
            $searchResult = mb_strpos($keySymbols, $currentSymbol, 0, self::ENCODING);
            
            if ($searchResult === false) {
                continue;
            }
                
            $stackSize = count($stack);
            $stackTop = ($stackSize > 0) ? $stack[$stackSize - 1] : null;
            
            if ($currentSymbol === '#' && !($stackTop === '#')) {
                array_push($stack, $currentSymbol);
            }
            else if ($currentSymbol === '{' && $stackTop === '#') {
                $newValue = array_pop($stack);
                array_push($stack, $newValue . $currentSymbol);
            }
            else if ($currentSymbol === '|' && !($stackTop === null || $stackTop === '#')) {
                /* no actions */
            }
            else if ($currentSymbol === '}' && $stackTop === '#{') {
                array_pop($stack);
            }
            else {
                return false;
            }
        }
        $stackSize = count($stack);
        
        if ($stackSize > 0) {
            return false;
        }
        return true;
    }
    
    private static function &getTemplateParts($pTemplate)
    {
        $temlateParts = array();
        $templatePart = '';        
        $templateLength = mb_strlen($pTemplate, self::ENCODING);
        $level = 0;
        
        for ($index = 0; $index < $templateLength; $index++) {
            $currentSymbol = mb_substr($pTemplate, $index, 1, self::ENCODING);
            
            if ($currentSymbol === '#') {
                $level++;
                
                if ($level === 1 && !($templatePart === '')) {
                    array_push($temlateParts, $templatePart);
                    $templatePart = '';
                }                
                $templatePart .= $currentSymbol;
            }
            else if ($currentSymbol === '}') {
                $templatePart .= $currentSymbol;
                
                if ($level === 1 && !($templatePart === '')) {
                    array_push($temlateParts, $templatePart);
                    $templatePart = '';                    
                }
                $level--;
            }
            else {
                $templatePart .= $currentSymbol;
            }
        }
        
        if (!($templatePart === '')) {
            array_push($temlateParts, $templatePart);
        }
    
        if (count($temlateParts) === 0) {
            array_push($temlateParts, '');
        }
        return $temlateParts;
    }
    
    private static function &getVariatorParts($pVariator)
    {
        $variatorParts = array();
        $variatorPart = '';        
        $variatorLength = mb_strlen($pVariator, self::ENCODING);
        $level = 0;
        
        for ($index = 0; $index < $variatorLength; $index++) {
            $currentSymbol = mb_substr($pVariator, $index, 1, self::ENCODING);
            
            if ($currentSymbol === '#') {
                $level++;
            }
                
            if ($currentSymbol === '#' && $level === 1) {
                /* no actions */
            }
            else if (($currentSymbol === '{' || $currentSymbol === '|' ||
                $currentSymbol === '}') && $level === 1) {
                array_push($variatorParts, $variatorPart);
                $variatorPart = '';                 
            }
            else {
                $variatorPart .= $currentSymbol;
            }
                
            if ($currentSymbol === '}') {
                $level--;
            }
        }
        return $variatorParts;
    }

    private static function disassembleTemplateRecursively($pTemplate, &$pRows)                       
    {
        $templateParts =& self::getTemplateParts($pTemplate);
        $templatePartsCount = count($templateParts);
        
        for ($index = 0; $index < $templatePartsCount; $index++) {
            $currentPart = $templateParts[$index];
            
            if (mb_substr($currentPart, 0, 1, self::ENCODING) === '#') {
                $variatorParts =& self::getVariatorParts($currentPart);
                $pRows[$index] = array(
                    'type' => 'V',
                    'name' => $variatorParts[0],
                    'values' => array()
                );
                $variatorPartsCount = count($variatorParts);
                
                for ($innerIndex = 1; $innerIndex < $variatorPartsCount; $innerIndex++) {
                    $currentVariatorPart = $variatorParts[$innerIndex];
                    $pRows[$index]['values'][$innerIndex - 1] = array(
                        'type' => 'P',
                        'values' => array()
                    );
                    self::disassembleTemplateRecursively($currentVariatorPart, 
                        $pRows[$index]['values'][$innerIndex - 1]['values']);
                }
            }
            else {
                $pRows[$index] = array('type' => 'S', 'value' => $currentPart);
            }
        }
    }
    
    private static function &disassembleTemplate($pTemplate)
    {
        $pRows = array();
        self::disassembleTemplateRecursively($pTemplate, $pRows);
        return $pRows;
    }

    private static function fillCompareTable(&$pCompareTable, &$pTemplateParts, $pFlag)
    {
        foreach ($pTemplateParts as $part) {
            if (!($part['type'] === 'V')) {
                continue;
            }
            
            $key = $part['name'];
            $variantsCount = count($part['values']);
                
            if ($pCompareTable[$key] === null) {
                $pCompareTable[$key] = array(
                    'inFirst' => false,
                    'inSecond' => false,
                    'notMatch' => false,
                    'count' => $variantsCount
                );                
            }
            
            $pCompareTable[$key][$pFlag] = true;
            
            if ($pCompareTable[$key]['count'] !== $variantsCount) {
                $pCompareTable[$key]['notMatch'] = true;
            }
        }
    }
    
    private static function &getOuterVariators(&$pTemplateParts1, &$pTemplateParts2)
    {
        $compareTable = array();
        self::fillCompareTable($compareTable, $pTemplateParts1, 'inFirst');
        self::fillCompareTable($compareTable, $pTemplateParts2, 'inSecond');        
        
        foreach ($compareTable as $key => $value) {
            if (!($value['inFirst'] && $value['inSecond'])) {
                return null;
            }
                
            if ($value['notMatch']) {
                return null;
            }
            $compareTable[$key] = $value['count'];
        }
        return $compareTable;
    }
    
    private static function verifyInnerVariatorsRecursively(&$pOuterVariators, &$pTemplateParts)
    {
        foreach ($pTemplateParts as $innerTemplatePart) {
            if (!($innerTemplatePart['type'] === 'V')) {
                continue;
            }
            
            $innerVariatorVariantsCount = count($innerTemplatePart['values']);
            $outerVariatorVariantsCount = $pOuterVariators[$innerTemplatePart['name']];

            if ($outerVariatorVariantsCount === null) {
                return false;
            }
            
            if (!($innerVariatorVariantsCount === $outerVariatorVariantsCount)) {
                return false;
            }
                
            foreach ($innerTemplatePart['values'] as $innerVariatorVariant) {
                $result = self::verifyInnerVariatorsRecursively($pOuterVariators, 
                    $innerVariatorVariant['values']);
                
                if (!$result) {
                    return false;
                }
            }
        }
        return true;
    }
    
    private static function verifyInnerVariators(&$pOuterVariators, &$pTemplateParts)
    {
        foreach ($pTemplateParts as $outerTemplatePart) {
            if (!($outerTemplatePart['type'] === 'V')) {
                continue;
            }
        
            foreach ($outerTemplatePart['values'] as $outerVariatorVariant) {
                $result = self::verifyInnerVariatorsRecursively($pOuterVariators, 
                    $outerVariatorVariant['values']);
                
                if (!$result) {
                    return false;
                }
            }
        }
        return true;
    }

    private static function incEnumerators(&$pVariatorsList, &$pVariatorsMap)
    {
        $variatorsCount = count($pVariatorsList);
        
        for ($index = 0; $index < $variatorsCount; $index++) {
            $currentValue = $pVariatorsMap[$pVariatorsList[$index]]['enum'];
            $maxValue = $pVariatorsMap[$pVariatorsList[$index]]['count'];
            
            if ($currentValue + 1 < $maxValue) {
                $pVariatorsMap[$pVariatorsList[$index]]['enum']++;
                return true;
            }
            $pVariatorsMap[$pVariatorsList[$index]]['enum'] = 0;
        }
        return false;
    }
    
    private static function createSentenceRecursively(&$pSentence, &$pVariators, 
        &$pTemplateParts)
    {
        foreach ($pTemplateParts as $templatePart) {
            if (!($templatePart['type'] === 'V')) {
                $pSentence .= $templatePart['value'];    
                continue;               
            }                   
            $variantIndex = $pVariators[$templatePart['name']]['enum'];
            $variant = $templatePart['values'][$variantIndex];
            self::createSentenceRecursively($pSentence, $pVariators, $variant['values']);
        }
    }
    
    private static function &createSentence(&$pVariators, &$pTemplateParts)
    {
        $sentence = '';
        self::createSentenceRecursively($sentence, $pVariators, $pTemplateParts);
        return $sentence;
    }
    
    private static function &createSentencesTable(&$pVariatorsMap, &$pTemplateParts1, 
        &$pTemplateParts2)
    {
        $variatorsList = array();
        
        foreach ($pVariatorsMap as $name => $count) {
            $pVariatorsMap[$name] = array('enum' => 0, 'count' => $count);
            array_push($variatorsList, $name);
        }
        
        $table = array();    
        $next = true;
        
        while ($next) {
            array_push($table, array(
                'sentence1' => self::createSentence($pVariatorsMap, $pTemplateParts1),
                'sentence2' => self::createSentence($pVariatorsMap, $pTemplateParts2)
            ));            
            $next = self::incEnumerators($variatorsList, $pVariatorsMap);
        }
        return $table;
    }
    
    private static function getTemplatesDescription($pResult1, $pResult2)
    {
        $desc = '';
        
        if (($pResult1 === false) && ($pResult2 === false)) {
            $desc = 'в шаблонах 1 и 2';
        }
        else if ($pResult1 === false) {
            $desc = 'в шаблоне 1';
        }
        else if ($pResult2 === false) {
            $desc = 'в шаблоне 2';
        }
        return $desc;
    }
    
    private static function addErrorDescription(&$pReturnStruct, $pError,
        $pResult1, $pResult2)
    {
        $errors = array();
        $desc = self::getTemplatesDescription($pResult1, $pResult2);
        array_push($errors, "Не соблюден порядок вложенности скобок {$desc} !!!");
        array_push($errors, "Не соблюден состав или количество вариантов внешних вариаторов {$desc} !!!");
        array_push($errors, "Cостав или количество вариантов вложенных вариаторов {$desc} "
        . "не соответствует внешним вариаторам !!!");
        
        $pReturnStruct['success'] = false;
        $pReturnStruct['error_description'] = $errors[$pError];    
    }
}