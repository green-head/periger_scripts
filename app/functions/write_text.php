<?php
function write_text($file_name, $content){
    if(!$file=fopen($file_name, 'w')) return false;
    if(fwrite($file, $content)===false) return false;
    fclose($file);
    return true;
}
?>