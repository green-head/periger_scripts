<?php
function random_keys($length=8,$between=[]){
	if(empty($between)){
		$between=['a', 'b', 'c', 'd', 'e', 'f', 'g',
		'h', 'i', 'j', 'k', 'l', 'm', 'n', 'p', 'q',
		'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
		'1', '2', '3', '4', '5', '6', '7', '8', '9',
		'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I',
		'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R',
		'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
    }
    settype($length,'integer');
    if($length<1) $length=8;
    $keys='';
    for($a=0; $a<$length; $a++)
    	$keys.=$between[rand(0, count($between)-1)];
    return $keys;
}
?>