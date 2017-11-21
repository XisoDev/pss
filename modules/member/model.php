<?php

class memberModel{

    function init(){

    }

    function getLoggedInfo(){
        if(isset($_SESSION['logged_info'])){
            if($_SESSION['logged_info']->member_srl < 1){
                return new Object(-1, "not_logged");
            }else{
                $output = new Object();
                $output->data = $_SESSION['logged_info'];
                return $output;
            }
        }else{
            return new Object(-1, "not_logged");
        }
    }

    function getMemberInfoByMemberSrl($member_srl = false){
        $output = new Object();
        $member_info = new stdClass();
        if(!$member_srl){
            $member_info->member_srl = 0;
        }

        $row = sql_fetch("select * from `member` where `member_srl` = '{$member_srl}'");
        if($row){
            $member_info = (object)$row;
        }else{
            $member_info->member_srl = 0;
        }

        $member_info->permissions = unserialize($member_info->permissions);
        if(!is_array($member_info->permissions)){
            $member_info->permissions = array();
        }
        $member_info->circu_srls = unserialize($member_info->circu_srls);
        if(!is_array($member_info->circu_srls)){
            $member_info->circu_srls = array();
        }
        $output->data = $member_info;
        return $output;
    }

}

?>