<?php

/*
 * Autor: Daniel Bośnjak
 * Email:daniel@pikselownia.com
 * Data utworzenia:2018-08-21 Kodowanie: UTF-8
 */
namespace pix\EncodingDecoders;


/**
 *@source https://en.wikipedia.org/wiki/Mazovia_encoding 
 **/
class MazoviaConverter
{

	const LETTER_MAP = [
			/** TABELKI **/
			0xb4 => 0x2d,
            0xb5 => 0x2d,
            0xb6 => 0x2d,
            0xbf => 0x2d,
            0xbb => 0x2d,
            0xc0 => 0x2d,
            0xc1 => 0x2d,
            0xc2 => 0x2d,
            0xc3 => 0x2d,
            0xc4 => 0x2d,
            0xc5 => 0x2d,
            0xc6 => 0x2d,
            0xc7 => 0x2d,
            0xc8 => 0x2d,
            0xd9 => 0x2d,
            0xda => 0x2d,
            /** znaki blokowe =>  | 0x7c */
            0xba => 0x7C,
            0xb3 => 0x7C,

            /**                  **/
            0x92 => 0xb3,
            0x8F => 0xA1,
            0x95 => 0xc6,
            0x90 => 0xca,
			0xa3 => 0xD3,
            0x9c => 0xa3,
            0xa5 => 0xD1,
			0xa6 => 0xbc,
            0x98 => 0xa6,
            0xa0 => 0xac,
            0xa1 => 0xaf,
            0x86 => 0xb1,
            0x8d => 0xe6,
            0x91 => 0xea,
            0xa4 => 0xf1,
            0xa2 => 0xf3,
            0x9e => 0xb6,
            0xa7 => 0xbf,

	];
	
    public static function decode_file($filename,$destination_encoding= 'UTF-8')
    {
        if (!file_exists($filename)) {
            return -1;
        }

        $handle = fopen($filename, "r");
        $output = '';
        if ($handle) {
            fgets($handle);
            fgets($handle);
            while (!feof($handle)) {
                $data = fgets($handle);
                $output .= self::decode_string($data,$destination_encoding);
            }
            fclose($handle);
      

            return  $output;
        } else {
            return -2;
        }
    }
	
	/**
     * Przeprowadza konwersję kodowania znaków z Mazovia CP432 na UTF-8. Odczyt pliku znak po znaku. 
     * @param array $args definuj parametry wejściowe
     * @return string plik wyjściowy
     */
    public static function decode_string($string,$destination_encoding= 'UTF-8')
    {
        static $find, $replace;
		
		if(empty($find)){
            $find = array_map('chr',array_keys(self::LETTER_MAP));
            $replace = array_map('chr',self::LETTER_MAP);
        }
        $string = str_replace($find,$replace,$string);
        return preg_replace('/[\x00-\x09]\0x10\0x16]/u', '', iconv('ISO-8859-2', $destination_encoding,$string));
    }
	
	   /**
     * Przeprowadza konwersję kodowania znaków z Mazovia CP432 na UTF-8. Odczyt pliku znak po znaku. 
     * @param array $args definuj parametry wejściowe
     * @return string plik wyjściowy
     */
    protected static function convert($string,$destination_encoding) {
        
        static $find, $replace;
		
		if(empty($find)){
            $find = array_map('chr',array_keys(self::LETTER_MAP));
            $replace = array_map('chr',self::LETTER_MAP);
        }
        $string = str_replace($find,$replace,$string);
        return preg_replace('/[\x00-\x09\x7F\x0B\0x0E\0x0F\0x10\0x16]/u', '', iconv('ISO-8859-2', $destination_encoding,$string));
    }


}
