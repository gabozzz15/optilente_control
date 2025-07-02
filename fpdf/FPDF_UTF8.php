<?php
require_once('fpdf.php');

/**
 * Clase extendida de FPDF para manejar correctamente caracteres UTF-8
 */
class FPDF_UTF8 extends FPDF
{
    /**
     * Sobreescribe el método Cell para manejar caracteres UTF-8
     */
    function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
    {
        // Convertir texto a UTF-8 si es necesario
        $txt = $this->utf8_to_win1252($txt);
        parent::Cell($w, $h, $txt, $border, $ln, $align, $fill, $link);
    }

    /**
     * Sobreescribe el método Write para manejar caracteres UTF-8
     */
    function Write($h, $txt, $link='')
    {
        // Convertir texto a UTF-8 si es necesario
        $txt = $this->utf8_to_win1252($txt);
        parent::Write($h, $txt, $link);
    }

    /**
     * Sobreescribe el método Text para manejar caracteres UTF-8
     */
    function Text($x, $y, $txt)
    {
        // Convertir texto a UTF-8 si es necesario
        $txt = $this->utf8_to_win1252($txt);
        parent::Text($x, $y, $txt);
    }

    /**
     * Sobreescribe el método MultiCell para manejar caracteres UTF-8
     */
    function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false)
    {
        // Convertir texto a UTF-8 si es necesario
        $txt = $this->utf8_to_win1252($txt);
        parent::MultiCell($w, $h, $txt, $border, $align, $fill);
    }

    /**
     * Convierte caracteres UTF-8 a Windows-1252 (ISO-8859-1)
     */
    protected function utf8_to_win1252($txt) {
        if (!mb_detect_encoding($txt, 'UTF-8', true)) {
            return $txt;
        }
        
        $win1252_chars = array(
            // Símbolos
            "\xE2\x82\xAC" => "\x80", // Euro
            "\xE2\x80\x9A" => "\x82", // Single low quote
            "\xC6\x92" => "\x83", // Florin
            "\xE2\x80\x9E" => "\x84", // Double low quote
            "\xE2\x80\xA6" => "\x85", // Ellipsis
            "\xE2\x80\xA0" => "\x86", // Dagger
            "\xE2\x80\xA1" => "\x87", // Double dagger
            "\xCB\x86" => "\x88", // Circumflex
            "\xE2\x80\xB0" => "\x89", // Per mille
            "\xC5\xA0" => "\x8A", // Capital S caron
            "\xE2\x80\xB9" => "\x8B", // Left single angle quote
            "\xC5\x92" => "\x8C", // Capital OE ligature
            "\xC5\xBD" => "\x8E", // Capital Z caron
            "\xE2\x80\x98" => "\x91", // Left single quote
            "\xE2\x80\x99" => "\x92", // Right single quote
            "\xE2\x80\x9C" => "\x93", // Left double quote
            "\xE2\x80\x9D" => "\x94", // Right double quote
            "\xE2\x80\xA2" => "\x95", // Bullet
            "\xE2\x80\x93" => "\x96", // En dash
            "\xE2\x80\x94" => "\x97", // Em dash
            "\xCB\x9C" => "\x98", // Small tilde
            "\xE2\x84\xA2" => "\x99", // Trademark
            "\xC5\xA1" => "\x9A", // Small s caron
            "\xE2\x80\xBA" => "\x9B", // Right single angle quote
            "\xC5\x93" => "\x9C", // Small oe ligature
            "\xC5\xBE" => "\x9E", // Small z caron
            "\xC5\xB8" => "\x9F", // Capital Y diaeresis
            
            // Caracteres acentuados y especiales
            "\xC3\x80" => "\xC0", // À
            "\xC3\x81" => "\xC1", // Á
            "\xC3\x82" => "\xC2", // Â
            "\xC3\x83" => "\xC3", // Ã
            "\xC3\x84" => "\xC4", // Ä
            "\xC3\x85" => "\xC5", // Å
            "\xC3\x86" => "\xC6", // Æ
            "\xC3\x87" => "\xC7", // Ç
            "\xC3\x88" => "\xC8", // È
            "\xC3\x89" => "\xC9", // É
            "\xC3\x8A" => "\xCA", // Ê
            "\xC3\x8B" => "\xCB", // Ë
            "\xC3\x8C" => "\xCC", // Ì
            "\xC3\x8D" => "\xCD", // Í
            "\xC3\x8E" => "\xCE", // Î
            "\xC3\x8F" => "\xCF", // Ï
            "\xC3\x90" => "\xD0", // Ð
            "\xC3\x91" => "\xD1", // Ñ
            "\xC3\x92" => "\xD2", // Ò
            "\xC3\x93" => "\xD3", // Ó
            "\xC3\x94" => "\xD4", // Ô
            "\xC3\x95" => "\xD5", // Õ
            "\xC3\x96" => "\xD6", // Ö
            "\xC3\x97" => "\xD7", // ×
            "\xC3\x98" => "\xD8", // Ø
            "\xC3\x99" => "\xD9", // Ù
            "\xC3\x9A" => "\xDA", // Ú
            "\xC3\x9B" => "\xDB", // Û
            "\xC3\x9C" => "\xDC", // Ü
            "\xC3\x9D" => "\xDD", // Ý
            "\xC3\x9E" => "\xDE", // Þ
            "\xC3\x9F" => "\xDF", // ß
            "\xC3\xA0" => "\xE0", // à
            "\xC3\xA1" => "\xE1", // á
            "\xC3\xA2" => "\xE2", // â
            "\xC3\xA3" => "\xE3", // ã
            "\xC3\xA4" => "\xE4", // ä
            "\xC3\xA5" => "\xE5", // å
            "\xC3\xA6" => "\xE6", // æ
            "\xC3\xA7" => "\xE7", // ç
            "\xC3\xA8" => "\xE8", // è
            "\xC3\xA9" => "\xE9", // é
            "\xC3\xAA" => "\xEA", // ê
            "\xC3\xAB" => "\xEB", // ë
            "\xC3\xAC" => "\xEC", // ì
            "\xC3\xAD" => "\xED", // í
            "\xC3\xAE" => "\xEE", // î
            "\xC3\xAF" => "\xEF", // ï
            "\xC3\xB0" => "\xF0", // ð
            "\xC3\xB1" => "\xF1", // ñ
            "\xC3\xB2" => "\xF2", // ò
            "\xC3\xB3" => "\xF3", // ó
            "\xC3\xB4" => "\xF4", // ô
            "\xC3\xB5" => "\xF5", // õ
            "\xC3\xB6" => "\xF6", // ö
            "\xC3\xB7" => "\xF7", // ÷
            "\xC3\xB8" => "\xF8", // ø
            "\xC3\xB9" => "\xF9", // ù
            "\xC3\xBA" => "\xFA", // ú
            "\xC3\xBB" => "\xFB", // û
            "\xC3\xBC" => "\xFC", // ü
            "\xC3\xBD" => "\xFD", // ý
            "\xC3\xBE" => "\xFE", // þ
            "\xC3\xBF" => "\xFF", // ÿ
        );
        
        return strtr($txt, $win1252_chars);
    }
}
?>