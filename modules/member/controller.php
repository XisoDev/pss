<?php

class memberController{

    function init(){

    }

    function procLogin($args){
        if(!$args->user_id || !$args->password){
            return setReturn(-1,"누락된 값이 있습니다.");
        }

        $sql = "select * from `member` where `user_id` = '{$args->user_id}'";
        $row = sql_fetch($sql);

        if(!$row) return setReturn(-1,"존재하지 않는 계정입니다.");
        if(sha1($args->password) != $row['password']) return setReturn(-1,"비밀번호가 일치하지 않습니다.");

        if($row['is_denied'] == "Y") return setReturn(-1, "승인되지 않은 계정입니다. 관리자에게 문의하세요.");

        $oMemberModel = getModel('member');

        $_SESSION['logged_info'] = $oMemberModel->getMemberInfoByMemberSrl($row['member_srl'])->data;
        return setReturn(0,"로그인 되었습니다.");
    }

    function procLogout(){
        global $domain;
        unset($_SESSION['logged_info']);

        //지정된 URL이 없으면 첫화면으로.
        if($_REQUEST['success_return_url']) $returnUrl = urldecode($_REQUEST['success_return_url']);
        else $returnUrl = $domain;

        header('Location: ' . $returnUrl);
    }
}

?>