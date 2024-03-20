<?php
function gen_pass($length=12){
    $alphabet = 'abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789!@#$%-*';
    $pass = array();
    $alphaLength = strlen($alphabet) - 1;
    for ($i = 0; $i < $length; $i++){
        $n = rand(0, $alphaLength);
        $pass[] = $alphabet[$n];
    }
    $pass=implode($pass);
	if (!preg_match('/^(?=.*\d)(?=.*[A-Za-z])[0-9A-Za-z!@#$%\-\*]{8,16}$/', $pass))
		return gen_pass($length);
	return $pass;
}
?>