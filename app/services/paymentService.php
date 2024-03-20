<?php
class paymentService extends Service{
    function init(){

    }
    function update_memberships($db_name){
        $db=new dbase($db_name);
        $now=date("Y-m-d")." 00:00:00";
        $rows=$db->query("SELECT * FROM `xuser_membership`
            WHERE `end_date`=? AND `ssi` IS NOT NULL AND `ssi`=0
            AND `status`=1", [ "s", $now ])->fetch_all();
        foreach($rows as $row){
            if(!$this->check_payment($row["ssi"])) continue;
        }
    }
    function check_payment(){

    }
}
?>