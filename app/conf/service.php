<?php
class Service
{
	protected $db;
	public $user;
	public $tCols=[
		"Bidlaw.users"=>["Bidlaw", "`users`", "`UserID`", "`Title`", "CONCAT('".BIDLAW_GALLERY_LINK."', `Photo`)"],
		"realestateusa.properties"=>["realestateusa", "`properties`", "`id`", "`full_address_new`", "''"],
		"realtor.realtor"=>["realtor", "`realtor`", "`id`", "`title`", "CONCAT('".REALTOR_LOGO."', `photo_url`)xs"],
		"admin_operism.businesses"=>["admin_operism", "`businesses`", "`id`", "`title`", "CONCAT('".OPERISM_LOGO."', `logo`)"],
		"users"=>[false, "`users`", "`id`", "CONCAT(`name`, ' ', `surname`)", "CONCAt('".GALLERY_LINK."', `pp`)"],
		"gtfs_stops"=>[false, "`gtfs_stops`", "`id`", "`name`", "''"],
		"bestplaces"=>[false, "`bestplaces`", "`id`", "`zip`", "''"],
		"article"=>[false, "`article`", "`id`", "`title`", "`ImageURL`"],
		"schools"=>[false, "`schools`", "`id`", "`title`", "''"],
		"poi"=>[false, "`poi`", "`id`", "`title`", "''"],
		"homes"=>[false, "`homes`", "`id`", "`title`", "`ImageURL`"]
	];
	function __construct()
	{
		$this->db = new dbase();
		$this->checkUserToken();
	}
	protected function checkUserToken()
	{
        if((isset($_POST['token'])) && (!empty($_POST['token']))){
            $token=$_POST['token'];
            $token_type=1;
        }
        elseif((isset($_POST['m_token'])) && (!empty($_POST['m_token']))){
            $token=$_POST['m_token'];
            $token_type=2;
        }
        elseif((isset($_POST['d_token'])) && (!empty($_POST['d_token']))){
            $token=$_POST['d_token'];
            $token_type=3;
        }
        else throw new Err("token_not_set");
        if(!$t=$this->check_token($token, $token_type))
            throw new Err("authentication_error");
		$dt=date("Y-m-d H:i:s");
        if(!$this->user=$this->db->query("SELECT `users`.*,
			`xuser_membership`.`membership_type`
            FROM `users`
            LEFT OUTER JOIN `xuser_membership`
            ON `users`.`id`=`xuser_membership`.`user_id`
            AND `xuser_membership`.`start_date`<'$dt'
            AND `xuser_membership`.`end_date`>'$dt'
            WHERE `users`.`id`=?", [
                "i", $t["user_id"]
        ])->fetch_array(MYSQLI_ASSOC))
            throw new Err("authentication_error");
	}
	protected function token($l = 250)
	{
		if ($l % 2 != 0) $l--;
		return bin2hex(random_bytes($l / 2));
	}
	public function guid()
	{
		mt_srand((float)microtime() * 10000); //optional for php 4.2.0 and up.
		$charid = strtoupper(md5(uniqid(rand(), true)));
		$hyphen = chr(45); // "-"
		$uuid = chr(123) // "{"
			. substr($charid, 0, 8) . $hyphen
			. substr($charid, 8, 4) . $hyphen
			. substr($charid, 12, 4) . $hyphen
			. substr($charid, 16, 4) . $hyphen
			. substr($charid, 20, 12)
			. chr(125); // "}"
		return trim($uuid, "{}");
	}
	protected function cof($fn, $p = [])
	{
		$flnm = S_FUNC . $fn . '.php';
		if (!file_exists($flnm)) throw new Err('not_found: '.S_FUNC . $fn . '.php');
		require_once($flnm);
		if (!is_array($p)) $p = [$p];
		return call_user_func_array($fn, $p);
	}
	protected function include_class($cl)
	{
		$flnm = S_CLS . $cl . '.php';
		if (!file_exists($flnm)) throw new Err(S_CLS . $cl . '.php');
		require_once($flnm);
	}
	protected function check_token($token, $type)
	{
		if (!isset($this->db)) return false;
		if (!$row = $this->db->query("SELECT * FROM `tokens`
            WHERE `token`=? AND `type`=?
            AND (`exp_date`>? OR `exp_date` IS NULL)", [
			'sis',
			$token,
			$type,
			date('Y-m-d H:i:s')
		])->fetch_array(MYSQLI_ASSOC))
			return false;
		return $row;
	}
    protected function update_token($user_id, $token_type = 1, $exp_after=false, $new_token = false)
    {
		if(!$new_token) $new_token=$this->token();
        $this->db->query("DELETE FROM `tokens`
            WHERE `type`=? AND `user_id`=?", [
            "ii", $token_type, $user_id
        ]);
        $exp_date=((!$exp_after) ? NULL : date("Y-m-d H:i:s", strtotime($exp_after)));
        $this->db->query("INSERT INTO `tokens`(
                `type`, `user_id`, `token`, `created_date`, `exp_date`
            ) VALUES(?, ?, ?, ?, ?)", [
                "iisss", $token_type, $user_id, $new_token,
                date("Y-m-d H:i:s"), $exp_date
        ]);
        return $new_token;
    }
	protected function remove_token($token_id)
	{
		if (!isset($this->db)) return false;
		$this->db->query("DELETE FROM `tokens` WHERE `id`=?", [
			'i', $token_id
		]);
	}
    protected function delete_expired_tokens(){
        $this->db->query("DELETE FROM `tokens`
            WHERE `exp_date`<? AND `exp_date` IS NOT NULL", [
                's', date("Y-m-d H:i:s")
        ]);
    }
	protected function filters($fltsob, $wh="", $p=[''], $post=false){
		if($post===false) $post=$_POST;
		foreach($fltsob as $flts){
            foreach($flts["filters"] as $ind=>$flt){
                if((!isset($post[$flt]))||($this->isempty($post[$flt]))) continue;
                $flt_vls=$post[$flt];
                if(!is_array($flt_vls)) $flt_vls=[$flt_vls];
                $wh.=" (";
				$n=false;
                foreach($flt_vls as $ftl_vl){
                    if(array_key_exists("fnc", $flts)) [$w, $fv, $t]=$flts["fnc"]($flts["fields"][$ind], $ftl_vl);
					else [$w, $fv, $t]=$this->op_filters($flts["fields"][$ind], $ftl_vl, $flt, $post);
					if(empty($w)) continue;
                    $wh.=$w." OR ";
                    $p[0].=$t;
					if(!is_array($fv)) $fv=[$fv];
                    $p=array_merge($p, $fv);
					$n=true;
                }
                if($n) $wh=substr($wh, 0, -3).") AND";
				else $wh=substr($wh, 0, -1);
            }
        }
		if(count($p)<2) $p=[];
		if(strlen($wh)>1) $wh='WHERE '.substr($wh, 0, -4);
		return [$wh, $p];
	}
	private function isempty(&$var) {
		return empty($var) || $var === '0';
	}
	protected function op_filters($field, $value, $pName, $post){
		$operators=["=", "<", ">", "<=", ">=", "!="];
		$operator="=";
		$oName='_'.$pName.'_operator';
		if((isset($post[$oName]))||(!empty($post[$oName])))
			$operator=$post[$oName];
		if(!in_array($operator, $operators)) $operator="=";
		$wh=$field.$operator."?";
		return [$wh, [$value], "i"];
	}
	protected function paging_fq(){
		$p=((isset($_POST['page']))&& (!empty($_POST['page'])) ? (int)$_POST['page'] : 1);
        $q=((isset($_POST['quantity']))&& (!empty($_POST['quantity'])) ? (int)$_POST['quantity'] : 12);
		$f=($p-1)*$q;
		return [$f, $q];
	}
	protected function getRef($RefTable, $RefID){
		if(!array_key_exists($RefTable, $this->tCols)) return [];
		[$d, $t, $f1, $f2, $f3]=$this->tCols[$RefTable];
		$sql="SELECT $f1, $f2, $f3 FROM $t WHERE $f1=?";
		$p=["i", $RefID];
		if($d===false) $db=$this->db;
		else $db=new dbase($d);
		if(!$r=$db->query($sql, $p)->fetch_array(MYSQLI_NUM))
			return [];
		if($d!==false) unset($db);
		return ["id"=>$r[0], "title"=>$r[1], "photo"=>$r[2]];
	}
}
