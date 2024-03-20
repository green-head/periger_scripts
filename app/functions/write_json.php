<?php
function write_json($file_name, $content){
    if(!$file=fopen($file_name, 'w')) return false;
    if(fwrite($file, json_encode($content))===false) return false;
    fclose($file);
    return true;
}
?>