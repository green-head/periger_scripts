<?php
class initService extends Service{
    public $prms=[];
    function init(){
        $js=$this->cof("read_json", "dt");
        $dt=(isset($js["date"]) ? $js["date"] : date("Y-m-d H:i:s"));
        $this->db=new dbase();
        $this->update_profiles($dt);
    }
    function update_profiles($dt){
        $this->db->query("SELECT `id` FROM `profiles`
            WHERE `status`=1 AND `last_update`>?", [
                "s", date("Y-m-d H:i:s")
        ]);
    }
    function update_profile($id, $wh=""){
        $prms=$this->get_profile_params($id);
        $sql=$this->homes_sql($prms)." $wh";
        $homes=$this->db->query($sql)->fetch_all();
        $this->get_points_by_parameters($homes, $prms);
        $this->get_points_by_bestplaces($homes, $prms);
        $this->dbo=new dbase("admin_operism", "admin_operism", "3Pd20et_");
        $this->delete_profile_points($id);
        foreach($homes as $home){
            $prmf=$home["prmf"];
            foreach($prms["homeParams"] as $prm){

            }
            $total=(int)$this->fetch_total_point($home, $prms);
            if(count($prms["schools"])!=0){
                $po=$this->fetch_near_schools_point($home, $prms);
                $total+=$po;
                $prmf.="schools: $po, ";
            }
            if(count($prms["poi"])!=0){
                $po=$this->fetch_near_poi_point($home, $prms);
                $total+=$po;
                $prmf.="poi: $po, ";
            }
            if(count($prms["businesses"])!=0){
                $po=$this->fetch_near_operism_point($home, $prms);
                $total+=$po;
                $prmf.="businesses: $po, ";
            }
        }
    }
    function delete_point($home_id, $pid){
        $this->db->query("DELETE FROM `profile_home_points` \r\n WHERE `home_id`=$home_id AND `profile_id`=$pid");
    }
    function delete_profile_points($pid){
        $this->db->query("DELETE FROM `profile_home_points` \r\n WHERE `profile_id`=$pid");
    }
    function insert_point($home_id, $pid, $point, $prmf=""){
        $this->db->query("INSERT INTO `profile_home_points`(`home_id`, `profile_id`, `point`, `prmf`) VALUES(?, ?, ?, ?)", ["iiis", $home_id, $pid, $point, $prmf]);
    }
    function get_profile_params($id){
        $stmt=$this->db->query("SELECT *
            FROM `profile_parameter_points`
            WHERE `profile_id`=?", [
                "i", $id
        ]);
        $prms=["homeParams"=>[], "allprms"=>[], "businesses"=>[], "poi"=>[], "schools"=>[]];
        while($row=$stmt->fetch_array()){
            if(!array_key_exists($row["table"], $prms))
                $prms[$row["table"]]=[];
            $prm=&$prms[$row["table"]];
            $npr="parameter";
            if($row["table"]=="poi" || $row["table"]=="businesses") $npr="value";
            if(!array_key_exists($row[$npr], $prm))
                $prm[$row[$npr]]=[];
            $prm[$row[$npr]][]=$row;
            $prms["allprms"]=$row;
            if($row["table"]!="homes") continue;
            if(in_array($row["parameter"], $prms["homeParams"])) continue;
            $prms["homeParams"][]=$row["parameter"];
        }
        return $prms;
    }
    function fetch_total_point($row, $prms){
        $total=(int)$row["otherPoints"];
        foreach($prms["homeParams"] as $prm){
            if(!array_key_exists($prm."Points", $row))
                continue;
            $total+=(int)$row[$prm."Points"];
        }
    }
    function fetch_near_schools_point($row, $prms){
        $total=0;
        if(empty($row["latitude"])||empty($row["longitude"])) return 0;
        foreach($prms["schools"] as $prm){
            $sql="";
            $co=0;
            foreach($prm as $p){
                $sql+="(SELECT " . $p["point"] . " FROM( \r\n SELECT `schools`.`id`, (3959 * acos(cos(radians('" . $row["latitude"] . "')) * cos(radians(`schools`.`latitude`)) * cos( radians(`schools`.`longitude`) - radians('" . $row["longitude"] . "')) + sin(radians('" . $row["latitude"] . "')) * sin(radians(`schools`.`latitude`)))) AS `distance` \r\n FROM `schools` \r\n WHERE `schools`.`" . $p["parameter"] . "`" . $p["value1"] . $p["value"] . " \r\n HAVING `distance`<" . $p["value2"] . " \r\n LIMIT 1 ) AS `tbl` \r\n ) AS `sql" . $co . "`, ";
                $co++;
            }
            if($sql=="") continue;
            $sql="SELECT ".substr($sql, 0, -2);
            $stmt=$this->db->query($sql);
            $row=$stmt->fetch_array();
            $point=0;
            foreach($row as $ind=>$ele){
                if($ele=="") continue;
                if(!is_numeric($ele)) continue;
                if($ele>$point) $point=$ele;
            }
            $total+=(int)$point;
        }
        return $total;
    }
    function fetch_near_poi_point($row, $prms){
        $total=0;
        if(empty($row["latitude"])||empty($row["longitude"])) return 0;
        $p=[""];
        foreach($prms["poi"] as $prm){
            $sql="";
            $co=0;
            foreach($prm as $p){
                $sql+="(SELECT " . $p["point"] . " FROM( \r\n SELECT `poi`.`id`, (3959 * acos(cos(radians('" . $row["latitude"] . "')) * cos(radians(`poi`.`latitude`)) * cos( radians(`poi`.`longitude`) - radians('" . $row["longitude"] . "')) + sin(radians('" . $row["latitude"] . "')) * sin(radians(`schools`.`latitude`)))) AS `distance` \r\n FROM `poi` \r\n WHERE `poi`.`id`=? \r\n HAVING `distance`<" . $p["value2"] . " \r\n LIMIT 1 ) AS `tbl` \r\n ) AS `sql" . $co . "`, ";
                $p[0].="i";
                $p[]=$p["value"];
                $co++;
            }
            if($sql=="") continue;
            $sql="SELECT ".substr($sql, 0, -2);
            $stmt=$this->db->query($sql, $p);
            $row=$stmt->fetch_array();
            $point=0;
            foreach($row as $ind=>$ele){
                if($ele=="") continue;
                if(!is_numeric($ele)) continue;
                if($ele>$point) $point=$ele;
            }
            $total+=(int)$point;
        }
        return $total;
    }
    function fetch_near_operism_point($row, $prms){
        $total=0;
        if(empty($row["latitude"])||empty($row["longitude"])) return 0;
        $p=[""];
        foreach($prms["businesses"] as $prm){
            $sql="";
            $co=0;
            foreach($prm as $p){
                $sql+="(SELECT " . $p["point"] . " FROM( \r\n SELECT `businesses`.`id`, (3959 * acos(cos(radians('" . $row["latitude"] . "')) * cos(radians(`businesses`.`lat`)) * cos( radians(`businesses`.`lon`) - radians('" . $row["longitude"] . "')) + sin(radians('" . $row["latitude"] . "')) * sin(radians(`businesses`.`lat`)))) AS `distance` \r\n FROM `businesses` \r\n WHERE `businesses`.`title` LIKE ? \r\n HAVING `distance`<" . $p["value2"] . " \r\n LIMIT 1 ) AS `tbl` \r\n ) AS `sql" . $co . "`, ";
                $p[0].="s";
                $p[]="%".$p["value"]."%";
                $co++;
            }
            if($sql=="") continue;
            $sql="SELECT ".substr($sql, 0, -2);
            $stmt=$this->db->query($sql, $p);
            $row=$stmt->fetch_array();
            $point=0;
            foreach($row as $ind=>$ele){
                if($ele=="") continue;
                if(!is_numeric($ele)) continue;
                if($ele>$point) $point=$ele;
            }
            $total+=(int)$point;
        }
        return $total;
    }
    function homes_sql($prms){
        $sql="";
        foreach($prms["allprms"] as $a){
            $p=$a["parameter"];
            $sql1="";
            if(($p == "yearBuilt")||($p == "yearRenovated")){
                $year=(int)date("Y");
                if($a["value"]==""){
                    $a["value1"]=$year-$a["value2"];
                    $a["value2"]=$year-$a["value1"];
                }
                else{
                    $a["value"]=$year-$a["value"];
                    if($a["value1"]=="<") $a["value1"]=">";
                    elseif($a["value1"]==">") $a["value1"]="<";
                }
            }
            elseif($p=="listingDate"){
                if($a["value"]==""){
                    $a["value1"]=date("Ymd", "-".strtotime($a["value2"]."days"));
                    $a["value2"]=date("Ymd", "-".strtotime($a["value1"]."days"));
                }
                else{
                    $a["value"]=date("Ymd", "-".strtotime($a["value"]."days"));
                    if($a["value1"]=="<") $a["value1"]=">";
                    elseif($a["value1"]==">") $a["value1"]="<";
                }
            }
            if($a["value1"]==""){
                if(is_numeric($a["value"]))
                    $sql1.="WHEN `homes`.`$p`=".$a["value"]." THEN '".$a["point"]."' \r\n ";
                else
                    $sql1.="WHEN `homes`.`$p` LIKE '%".$a["value"]."%' THEN '".$a["point"]."' \r\n ";
            }
            elseif($a["value"]==""){
                if(is_numeric($a["value1"]))
                    $sql1.="WHEN `homes`.`$p` BETWEEN ".$a["value1"]." AND ".$a["value2"]." THEN '".$a["point"]."' \r\n ";
                else
                    $sql1.="WHEN `homes`.`$p` BETWEEN '".$a["value1"]."' AND '".$a["value2"]."' THEN '".$a["point"]."' \r\n ";
            }
            else
                $sql1.="WHEN `homes`.`$p` ".$a["value1"]." '".$a["value"]."' THEN ".$a["point"]." \r\n ";
            if(empty($sql1)) continue;
            $sql.= " CASE \r\n $sql1 \r\n ELSE '0' \r\n END AS `".$p."Points`, ";
        }
        $sql = "SELECT $sql `homes`.`id`, `homes`.`latitude`, `homes`.`longitude`, `homes`.`zipCode`, 0 AS `otherPoints`, '' AS `prmf` \r\n FROM `homes` \r\n";
        return $sql;
    }
    function get_points_by_parameters(&$homes, $prms){
        $sql="";
        $co=0;
        foreach($prms["parameters"] as $i=>$a){
            $sql2=$this->prm_to_point_sql($a);
            if($sql2=="") continue;
            $sql.="( SELECT '".$a["point"]."' FROM `Rel_homes_Details` \r\n WHERE `valueKey`='".$a["parameter"]."' AND IDHomes=? AND $sql2 \r\n LIMIT 1) AS `$i`, ";
            $co++;
        }
        if($co==0) return;
        $sql="SELECT ".substr($sql, 0, -2);
        foreach($homes as $i=>$home){
            $p=array_fill(1, $co, $home["id"]);
            $p[0]=str_repeat("i", $co);
            $stmt=$this->db->query($sql, $p);
            while($row=$stmt->fetch_array()){
                foreach($row as $ind=>$ele){
                    if($ele=="") continue;
                    $a=$prms["parameters"][$ind];
                    $homes[$i]["prmf"] .= $a["parameter"].":".$ele.",";
                    $homes[$i]["otherPoints"]=((int)$homes[$i]["otherPoints"])+((int)$ele);
                }
            }
        }
    }
    function get_points_by_bestplaces(&$homes, $prms){
        $sql="";
        $co=0;
        foreach($prms["bestplaces"] as $i=>$a){
            $sql2=$this->prm_to_point_sql($a, $a["parameter"]);
            if($sql2=="") continue;
            $sql.="( SELECT '".$a["point"]."' FROM `bestplaces` \r\n WHERE `zip`=? AND $sql2 \r\n LIMIT 1) AS `$i`, ";
            $co++;
        }
        if($co==0) return;
        $sql="SELECT ".substr($sql, 0, -2);
        foreach($homes as $i=>$home){
            $p=array_fill(1, $co, $home["zipCode"]);
            $p[0]=str_repeat("i", $co);
            $stmt=$this->db->query($sql, $p);
            while($row=$stmt->fetch_array(MYSQLI_NUM)){
                foreach($row as $ele){
                    if($ele=="") continue;
                    $homes[$i]["otherPoints"]=((int)$homes[$i]["otherPoints"])+((int)$ele);
                }
            }
        }
    }
    function get_points_by_agents(&$homes, $prms){
        $sql="";
        $co=0;
        foreach($prms["agents"] as $i=>$a){
            $sql2=$this->prm_to_point_sql($a, $a["parameter"]);
            if($sql2=="") continue;
            $sql.="( SELECT '".$a["point"]."' FROM `Rel_homes_realtor` \r\n WHERE `homes_id`=? AND $sql2 \r\n LIMIT 1) AS `$i`, ";
            $co++;
        }
        if($co==0) return;
        $sql="SELECT ".substr($sql, 0, -2);
        foreach($homes as $i=>$home){
            $p=array_fill(1, $co, $home["id"]);
            $p[0]=str_repeat("i", $co);
            $stmt=$this->db->query($sql, $p);
            while($row=$stmt->fetch_array(MYSQLI_NUM)){
                foreach($row as $ele){
                    if($ele=="") continue;
                    $homes[$i]["otherPoints"]=((int)$homes[$i]["otherPoints"])+((int)$ele);
                }
            }
        }
    }
    function prm_to_point_sql($a, $val="value"){
        $sql="";
        if(($a["value1"]=="")&&($a["value2"]=="")){
            if(is_numeric($a["value"]))
                $sql.=" `$val` = ".$a["value"];
            else $sql.=" `$val` LIKE '%".$a["value"]."%'";
        }
        else{
            if($a["value2"]=="")
                $sql.=" `$val` BETWEEN ".$a["value"]." AND ".$a["value1"];
            else $sql.=" `$val` ".$a["value2"]." ".$a["value"];
        }
        return $sql;
    }
}
?>