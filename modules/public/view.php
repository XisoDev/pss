<?php

class publicView {

    //    공통 실행
    function init(){
        //임시로그인
//        $_SESSION['logged_info'] = new stdClass();
//        $_SESSION['logged_info']->member_srl = 4;

        //로그인 안되어있으면 로그인페이지로
        $oMemberModel = getModel('member');
        $logged_info = $oMemberModel->getLoggedInfo();
        if($logged_info->error == -1){
            $current_url = "http://" . $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];
            header('Location: /member/login/?success_return_url=' . urlencode($current_url));
        }
    }

//    첫페이지.
    function index(){
        global $module_info;

        $module_info->template_file = "index";
    }
}

?>