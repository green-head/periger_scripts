<?php
function cr_unique_key($db, $table, $column, $cr_func){
	$unique_key=$cr_func();
	if($db->query("SELECT COUNT(*) AS [co] FROM $table WHERE $column=?", $unique_key)->fetch_array()['co']==0) return $unique_key;
	else return cr_unique_key($db, $table, $column, $cr_func);
}
?>