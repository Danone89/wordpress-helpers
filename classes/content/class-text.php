<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Wordpress_helpers\classes\content;

/**
 * Description of PixText
 *
 * @author Daniel
 */
final class Text
{
    const loremIpsum = " Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean accumsan, tellus sit amet aliquam placerat.
    Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean accumsan, tellus sit amet aliquam placerat.
    Lorem ipsum dolor sit amet, consectetur adipiscing elit. ";

    static function loremIpsum($lenght = 250, $echo = false)
    {
        $lorem = self::loremIpsum;
        if ($echo) {
            echo $lorem;
            return;
        }
        return $lorem;
    }
    /**
     * Default add noopener and nofollow
     *
     * @param [type] $content
     * @param array $args noopener => (bool), nofollow => (bool)
     * @return void
     */
    static function add_rels($content,$args = []){


    }

    static function add_noopener($content, $skip = '')
    {

    }
    static function add_no_follow($content, $skip = '')
    {

        return preg_replace_callback(
            "#(<a[^>]+?)>#is",
            function ($mach) use ($skip) {
                return (!($skip && strpos($mach[1], $skip) !== false) &&
                    strpos($mach[1], 'rel=') === false) ? $mach[1] . ' rel="nofollow">' : $mach[0];
            },
            $content
        );
    }
    static function add_nofollow($content)
    {
        $content = preg_replace_callback(
            '~<(a\s[^>]+)>~isU',
            function ($match) {
                list($original, $tag) = $match;   // regex match groups
                //$my_folder =  "/abc-artelis";       // re-add quirky config here
                if (mb_strpos($tag, "nofollow")) {
                    return $original;
                } elseif (mb_strpos($tag, (string) 'skapiec') === false) {

                    return $original;
                } else {
                    return "<$tag rel=\"nofollow\">";
                }
            },
            $content
        );

        return $content;
    }


    /**
     * get the limit of content of posts
     * @author James
     */
    static function limit_content($str, $length)
    {
        $str = strip_tags($str);
        $str = str_replace('[/', '[', $str);
        $str = strip_shortcodes($str);
        $str = explode(" ", $str);
        return implode(" ", array_slice($str, 0, $length));
    }

    static function rand_string()
    {
        $length = 20;
        $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
        $new_key = '';

        for ($p = 0; $p < $length; $p++) {
            $new_key .= $characters[mt_rand(0, strlen($characters) - 1)];
        }
        return $new_key;
    }

    /**
     * Truncates text.
     *
     * Cuts a string to the length of $length and replaces the last characters
     * with the ending if the text is longer than length.
     *
     * @param string  $text String to truncate.
     * @param integer $length Length of returned string, including ellipsis.
     * @param string  $ending Ending to be appended to the trimmed string.
     * @param boolean $exact If false, $text will not be cut mid-word
     * @param boolean $considerHtml If true, HTML tags would be handled correctly
     * @return string Trimmed string.
     */
    static function truncate($text, $length = 100, $ending = '...', $exact = true, $considerHtml = false)
    {
        if ($considerHtml) {
            // if the plain text is shorter than the maximum length, return the whole text
            if (mb_strlen(preg_replace('/<.*?>/', '', $text)) <= $length) {
                return $text;
            }

            // splits all html-tags to scanable lines
            preg_match_all('/(<.+?>)?([^<>]*)/s', $text, $lines, PREG_SET_ORDER);

            $total_length = mb_strlen($ending);
            $open_tags = array();
            $truncate = '';

            foreach ($lines as $line_matchings) {
                // if there is any html-tag in this line, handle it and add it (uncounted) to the output
                if (!empty($line_matchings[1])) {
                    // if it's an "empty element" with or without xhtml-conform closing slash (f.e. <br/>)
                    if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) {
                        // do nothing
                        // if tag is a closing tag (f.e. </b>)
                    } else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) {
                        // delete tag from $open_tags list
                        $pos = array_search($tag_matchings[1], $open_tags);
                        if ($pos !== false) {
                            unset($open_tags[$pos]);
                        }
                        // if tag is an opening tag (f.e. <b>)
                    } else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) {
                        // add tag to the beginning of $open_tags list
                        array_unshift($open_tags, strtolower($tag_matchings[1]));
                    }
                    // add html-tag to $truncate'd text
                    $truncate .= $line_matchings[1];
                }

                // calculate the length of the plain text part of the line; handle entities as one character
                $content_length = mb_strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
                if ($total_length + $content_length > $length) {
                    // the number of characters which are left
                    $left = $length - $total_length;
                    $entities_length = 0;
                    // search for html entities
                    if (preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) {
                        // calculate the real length of all entities in the legal range
                        foreach ($entities[0] as $entity) {
                            if ($entity[1] + 1 - $entities_length <= $left) {
                                $left--;
                                $entities_length += strlen($entity[0]);
                            } else {
                                // no more characters left
                                break;
                            }
                        }
                    }
                    $truncate .= mb_substr($line_matchings[2], 0, $left + $entities_length);
                    // maximum lenght is reached, so get off the loop
                    break;
                } else {
                    $truncate .= $line_matchings[2];
                    $total_length += $content_length;
                }

                // if the maximum length is reached, get off the loop
                if ($total_length >= $length) {
                    break;
                }
            }
        } else {
            $text = strip_tags($text);
            if (strlen($text) <= $length) {
                return $text;
            } else {
                $truncate = mb_substr($text, 0, $length - strlen($ending));
            }
        }

        // if the words shouldn't be cut in the middle...
        if (!$exact) {
            // ...search the last occurance of a space...
            $spacepos = mb_strrpos($truncate, ' ');
            if (isset($spacepos)) {
                // ...and cut the text in this position
                $truncate = mb_substr($truncate, 0, $spacepos);
            }
        }

        // add the defined ending to the text
        $truncate .= $ending;

        if ($considerHtml) {
            // close all unclosed html-tags
            foreach ($open_tags as $tag) {
                $truncate .= '</' . $tag . '>';
            }
        }

        return $truncate;
    }

    /**
     * Explode string to array with escaping quote
     */
    static function csv_explode($delim = ',', $str, $enclose = '"', $preserve = false)
    {
        $resArr = array();
        $n = 0;
        $expEncArr = explode($enclose, $str);
        foreach ($expEncArr as $EncItem) {
            if ($n++ % 2) {
                array_push($resArr, array_pop($resArr) . ($preserve ? $enclose : '') . $EncItem . ($preserve ? $enclose : ''));
            } else {
                $expDelArr = explode($delim, $EncItem);
                array_push($resArr, array_pop($resArr) . array_shift($expDelArr));
                $resArr = array_merge($resArr, $expDelArr);
            }
        }
        return $resArr;
    }

    /**
     * Convert for Unicode
     * @author James
     */
    static function ascii_to_entities($str)
    {
        $count = 1;
        $out = '';
        $temp = array();

        for ($i = 0, $s = strlen($str); $i < $s; $i++) {
            $ordinal = ord($str[$i]);

            if ($ordinal < 128) {
                if (count($temp) == 1) {
                    $out .= '&#' . array_shift($temp) . ';';
                    $count = 1;
                }

                $out .= $str[$i];
            } else {
                if (count($temp) == 0) {
                    $count = ($ordinal < 224) ? 2 : 3;
                }

                $temp[] = $ordinal;

                if (count($temp) == $count) {
                    $number = ($count == 3) ? (($temp['0'] % 16) * 4096) +
                        (($temp['1'] % 64) * 64) +
                        ($temp['2'] % 64) : (($temp['0'] % 32) * 64) +
                        ($temp['1'] % 64);

                    $out .= '&#' . $number . ';';
                    $count = 1;
                    $temp = array();
                }
            }
        }

        return $out;
    }
}
