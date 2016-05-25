<?php

namespace Sip\Lib;

class Tokenizer
{
    const ENCODING = 'utf-8';
    const PATTERN = '([A-Za-zА-Яа-я0-9\'\"\-])';

    public static function toString($pSentence)
    {
        mb_regex_encoding(self::ENCODING);

        $divSentence = '';
        $sentence = trim($pSentence);
        $sentenceLength = mb_strlen($sentence, self::ENCODING);

        if ($sentenceLength > 0) {
            $firstSymbol = mb_substr($sentence, 0, 1, self::ENCODING);
            $divSentence = mb_strtoupper($firstSymbol, self::ENCODING);
        }

        $isDivider = false;

        for ($index = 1; $index < $sentenceLength; $index++) {
            $prevSymbol = mb_substr($sentence, $index - 1, 1, self::ENCODING);
            $currSymbol = mb_substr($sentence, $index, 1, self::ENCODING);
            $prevSymbolType = self::getSymbolType($prevSymbol);
            $currSymbolType = self::getSymbolType($currSymbol);

            if ($prevSymbolType !== $currSymbolType && !($isDivider)) {
                $divSentence .= '|';
                $isDivider = true;
            }

            if ($currSymbolType > 0) {
                $divSentence .= mb_strtoupper($currSymbol, self::ENCODING);
                $isDivider = false;
            }
        }
        return $divSentence;
    }

    private static function getSymbolType($pSymbol)
    {
        if ($pSymbol === "\x9" || $pSymbol === "\x20") {
            return 0;
        }
        else if (mb_ereg_match(self::PATTERN, $pSymbol)) {
            return 1;
        }
        else {
            return 2;
        }
    }
}