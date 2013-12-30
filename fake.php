<?php
require 'config.php';

class Fake {
    private $config;
    private $source_text = 'text.txt';  
    private $dictionary = array();  
    private $last = array();
    private $largest_word;
    private $result_text;

    function __construct() {
        $this->config = new Config();
        $this->sentence_split();
        array_map(array($this, 'check_single_words'), $this->last);
        //$this->remove_all_empty_items();    // fastest, but less realistic algorithm
        $this->dictionary_closing();      // slow, but best realistic algorithm needs to fix;
        $this->set_largest_word();
    }

    function sentence_split() {  
        $str = file_get_contents($this->source_text);  
        $str = mb_convert_case($str, MB_CASE_LOWER, "UTF-8");
        $str = preg_replace('~[^\p{L} ,.!?-]++~u', '', $str);
        $str = preg_split('/[\.!?]+/u', $str);
        foreach ($str as $st) {
            $res = preg_split('/\b(?=\p{P} )|\b /u', trim($st));
            $this->add_to_dictionary($res);
        }
    }

    function check_single_words($key) {
        if (count($this->dictionary[trim($key)]) > 0) {
            $this->last = array_diff($this->last, array($key));
        }
    }

    function dictionary_closing() {
        $keys = array_keys($this->dictionary);
        $this->last = array_values($this->last);
        $this->set_largest_word();
        for ($i = 0; $i < count($this->last) ; $i++) {
            $this->dictionary[$this->last[$i]][] = $this->largest_word;
        }
    }

    function remove_all_empty_items() {
        while (true) {
            $empty = $this->get_empty_words();
            if (count($empty) == 0) {
                return;
            }
            foreach ($empty as $emp) {
                $this->remove_item($emp);
            }
        }
    }

    function get_empty_words() {
        $result = array();
        foreach ($this->dictionary as $key => $dict) {
            if (count($dict) == 0) {
                $result[] = $key;
            }
        }
        return $result;
    }

    function remove_item($item) {
        foreach ($this->dictionary as $key => $val) {
            if (in_array($item, $val)) {
                $this->dictionary[$key] = array_diff($val, array($item));
            }
        }
        $this->last = array_diff($this->last, array($item));
        unset($this->dictionary[$item]);
    }

    function add_to_dictionary($arr) {
        if (trim(end($arr)) && !in_array(end($arr), $this->last)) {
            $this->last[] = trim(end($arr));
        }

        for ($i = 0; $i < count($arr); $i++) {
            if (trim($arr[$i]) && !array_key_exists(trim($arr[$i]), $this->dictionary)) {
                $this->dictionary[trim($arr[$i])] = array();
            }
            if (array_key_exists($i + 1, $arr) && trim($arr[$i + 1])) {
                $this->dictionary[trim($arr[$i])][] = trim($arr[$i + 1]);
            }
        }
    }

    function set_largest_word() {
        $count = 0;
        $result_key = null;
        foreach ($this->dictionary as $key => $value) {
            if (count($value) > $count) {
                $count = count($value);
                $result_key = $key;
            }
        }
        $this->largest_word = $result_key;
    }

    function generate_text($count, $paragraph = null) {  
        $words = array_keys($this->dictionary);  
        $text = '';
        if ($paragraph) {
            $result_text = $paragraph;  
        } else {
            $result_text = '';
        }

        $first_upper = true;
        while (true) {
            $words_in_sentence = rand($this->config->text_words_in_sentence[0], $this->config->text_words_in_sentence[1]);
            $sentences_in_paragraph = rand($this->config->text_sentences_in_paragraph[0], 
                $this->config->text_sentences_in_paragraph[1]);
            $word = $words[rand(0, count($this->dictionary) - 1)];  
            for ($n = 0; $n < $sentences_in_paragraph; $n++) {
                for ($i = 0; $i < $words_in_sentence; $i++) {
                    if($first_upper) {
                        $word_append = first_to_upper($word);
                        $first_upper = false;
                    } elseif ($word[0] == ',') {
                        $word_append = $word;
                    } else {
                        $word_append = ' ' . $word;
                    }
                    $text .= $word_append;  
                    $result_text .= $word_append;  
                    if (mb_strlen($text, 'UTF-8') >= $count && mb_strlen($word, 'UTF-8') > 3) {
                        $result_text .= "."; 
                        $this->result_text = $result_text;
                        return;
                    }
                    if($i == $words_in_sentence - 1 && mb_strlen($word, 'UTF-8') <= 3) {
                        $i = $i - 1;
                    }
                    $word = $this->dictionary[$word][rand(0,count($this->dictionary[$word])-1)];  
                }
                $text .= '. ';
                $result_text .= '. ';
                $first_upper = true;
            }
            if ($paragraph) {
                $result_text .= '<br>' . $paragraph;
            }
        }  
    }  

    function text($count) {
        $this->generate_text($count, '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;');
        return $this->result_text;
    }

    function solid($count) {
        $this->generate_text($count);
        return $this->result_text;
    }

    function title($count) {
        $res = $this->solid($count);
        return substr($res, 0, strlen($res) - 1);
    }
}

function first_to_upper($str) {
    if (trim($str)[0] == ',') {
        $str = substr($str, 2);
    }
    $str = trim($str);
    $str = mb_strtoupper(mb_substr($str, 0, 1, 'UTF-8'), 'UTF-8') . mb_substr($str, 1, mb_strlen($str), 'UTF-8');
    return $str;
}



?>
