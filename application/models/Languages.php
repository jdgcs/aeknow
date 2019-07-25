<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Languages extends CI_Model {
	
	public function getPreferredLanguage() {  
	//Get browser's language
    $langs = array();  
    if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {  
        // break up string into pieces (languages and q factors)  
        preg_match_all('/([a-z]{1,8}(-[a-z]{1,8})?)s*(;s*qs*=s*(1|0.[0-9]+))?/i',  
                $_SERVER['HTTP_ACCEPT_LANGUAGE'], $lang_parse);  
        if (count($lang_parse[1])) {  
            // create a list like "en" => 0.8  
            $langs = array_combine($lang_parse[1], $lang_parse[4]);  
            // set default to 1 for any without q factor  
            foreach ($langs as $lang => $val) {  
                if ($val === '') $langs[$lang] = 1;  
            }  
            // sort list based on value  
            arsort($langs, SORT_NUMERIC);  
        }  
    }  
    //extract most important (first)  
    foreach ($langs as $lang => $val) { break; }  
    //if complex language simplify it  
    if (stristr($lang,"-")) {$tmp = explode("-",$lang); $lang = $tmp[0]; }  
    //Chinese and English only before other languages are supported
    if($lang!="zh"){$lang="en";}
    return $lang;  
} 
}
