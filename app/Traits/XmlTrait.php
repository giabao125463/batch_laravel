<?php
namespace App\Traits;

use App\Helpers\StringHelper;

/**
 * Trait XmlTrait
 */
trait XmlTrait
{
    /**
     * Auto append space characters
     *
     * @param string $text
     * @param int $length
     * @return string
     */
    public function fillWidthWithSpace($text, $length)
    {
        $text   = $this->removeSpecialCharacters($text);
        $len    = StringHelper::mbStringWidth($text);
        if ($len > $length) {
            $text = StringHelper::subString($text, $length);
        }
        $len    = StringHelper::mbStringWidth($text);
        while ($len < $length) {
            $text .= ' ';
            $len++;
        }

        return $text;
    }

    /**
     * Remove special characters for exporting XML
     *
     * @param string $text
     * @return string
     */
    public function removeSpecialCharacters($text)
    {
        $characters = ['<', '>', '&', "'", '"'];
        foreach($characters as $char) {
            $text = str_replace($char, '', $text);
        }

        return $text;
    }
}