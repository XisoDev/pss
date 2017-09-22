<?php

class memberView{
    function init(){
        
    }
    
    function login(){
        global $module_info;

        //로그인 안되어있으면 로그인페이지로
        $oMemberModel = getModel('member');
        $logged_info = $oMemberModel->getLoggedInfo();
        if($logged_info->error == -1){
            $module_info->template_file = "login";
        }else{
            $module_info->template_file = "logged";
        }
    }
}

?>