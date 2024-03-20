<?php
function read_json($file_name){
    if(!$file=fopen($file_name, 'r')) return false;
    $content=fread($file, filesize($file_name));
    fclose($file);
    $array=json_decode($content, true);
    if($array==NULL) return false;
    return $array;
}
?>