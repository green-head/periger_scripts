<?php
function decode_json($str){
	if(is_null($str)||($str=='')||($str==0)||($str=='0')||($str=='NULL')||($str=='null'))
		return [];
	return json_decode($str, true);
}
?>