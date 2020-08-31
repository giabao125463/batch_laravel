<?php

namespace App\Helpers;

class StringHelper
{
    /**
     * SubString
     *
     * There will be some errors when substring with 2 bytes characters
     * First of all, original string must be converted to 2 bytes characters (SJIS) for unified character format
     * before using `strlen` or `mb_strcut`
     *
     * @param string $string
     * @param integer $length
     * @param integer $index
     * @return string
     */
    public static function subString(string $string, int $length, int $index = 0)
    {
        if (empty($string)) {

            return null;
        }
        $convertText = mb_convert_encoding($string, 'SJIS');
        $len         = strlen($convertText);
        if ($len > $length) {
            $convertText = mb_strcut($convertText, $index, $length, 'SJIS');
        }

        return mb_convert_encoding($convertText, 'UTF-8', 'SJIS');
    }

    /**
     * Count string's width
     *
     * @param string $string
     * @return integer
     */
    public static function mbStringWidth($string)
    {
        $convertText = mb_convert_encoding($string, 'SJIS');
        return strlen($convertText);
    }

    /**
     * Remove white space in text
     *
     * @param string $text
     * @param boolean $mbSpace Remove 2 bytes space or not
     * @return string
     */
    public static function removeWhiteSpace($text, $mbSpace = true)
    {
        $regex = '/[ ]/u';
        if ($mbSpace) {
            $regex = '/[ 　]/u';
        }
        return preg_replace($regex, '', $text);
    }
}