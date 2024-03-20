<?php
class dbase
{
	public $db;
	function __construct($dbase = "emlak", $user_name="qors", $password="8lJ35UHKsFGw8Mzr8fWtZR9O", $ip="localhost")
	{
		$this->db = @new mysqli($ip, $user_name, $password, $dbase);
		if ($this->db->connect_errno){
			die();
			var_dump($ip, $user_name, $password, $dbase);
			die("connection_error");
		}
		//throw new Exception('database|connectionerror');
		mysqli_set_charset($this->db, "utf8");
	}
	public function refValues($arr)
	{
		if (strnatcmp(phpversion(), '5.3') >= 0) { //Reference is required for PHP 5.3+
			$refs = array();
			foreach ($arr as $key => $value)
				$refs[$key] = &$arr[$key];
			return $refs;
		}
		return $arr;
	}
	function query($sql, $params = [])
	{
		$stmt = $this->db->prepare($sql);
		if ($stmt === false) {
			var_dump($sql);
			var_dump($params);
			var_dump($this->db->error);
			die();
		}
		if (!empty($params))
			call_user_func_array(array($stmt, 'bind_param'), $this->refValues($params));
		//if($stmt===false) throw new Exception('database|queryerror');
		if ($stmt === false) {
			var_dump($sql);
			var_dump($params);
			var_dump($this->db->error);
			die();
		}
		$stmt->execute();
		$sonuc = $stmt->get_result();
		return new stmt($sonuc);
	}
	function insert_id()
	{
		return $this->db->insert_id;
	}
	function __destruct()
	{
		$this->db->close();
	}
}
class stmt{
	public $mstmt;
	public $num_rows=0;
	function __construct($stm)
	{
		$this->mstmt=$stm;
		$this->num_rows=$stm->num_rows;
	}
	function fetch_array($t=MYSQLI_ASSOC){
		return $this->mstmt->fetch_array($t);
	}
	function fetch_all($t=MYSQLI_ASSOC){
		return $this->mstmt->fetch_all($t);
	}
	function fetch_column($ind=0){
		$arr=[];
		while($row=$this->mstmt->fetch_array(MYSQLI_NUM)){
			$arr[]=$row[$ind];
		}
		return $arr;
	}
}