<?php
class Success extends Exception{
	protected $ms;
	function __construct($ms){
		$this->ms=$ms;
		parent::__construct('e');
	}
	function getVarMessage(){
		return $this->ms;
	}
}
?>