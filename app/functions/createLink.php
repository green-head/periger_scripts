<?php
function createLink($link='',$return=false, $noLang=false){
    $langShortCode = '';
    if(!defined('BASEURL'))
        die('Base URL not set!');
    if(!$noLang){
        if(defined('LANG_SHORT_CODE'))
               $langShortCode = LANG_SHORT_CODE.'/';
    }
    if($return)
        return BASEURL.$langShortCode.$link;
    else
        echo BASEURL.$langShortCode.$link;
}
?>