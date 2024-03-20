<?php
function read_text($file_name){
    if(!$file=fopen($file_name, 'r')) return false;
    $content=fread($file, filesize($file_name));
    fclose($file);
    if($content==NULL) return false;
    return $content;
}
?>