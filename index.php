<?php

define('_XISO_VERSION_','2.0.0');
//rewrite rule to ModuleInfo.
$module_info = new stdClass();
if(isset($_GET['vid'])) {
    if($_GET['vid'] == "settings"){
        $module_info->is_admin = "Y";
        $module_info->module = "settings";
    }else{
        $module_info->is_admin = "N";
        $module_info->module = $_GET['vid'];
    }

    if(isset($_GET['mid'])) $module_info->act = $_GET['mid'];
    else $module_info->act = "index";

}else if(isset($_GET['mid'])){
    if($_GET['mid'] == "settings"){
        $module_info->is_admin = "Y";
        $module_info->act = "index";
        $module_info->module = "settings";
    }else{
        $module_info->is_admin = "N";
        $module_info->module = $_GET['mid'];
        $module_info->act = "index";
    }
}else{
    $module_info->is_admin = "N";
    $module_info->act = "index";
    $module_info->module = "public";
}

if(substr($module_info->act,0,4) == "proc"){
    $module_info->mode = "controller";
    $module_info->class = $module_info->module."Controller";
}else if(substr($module_info->act,0,3) == "get"){
    $module_info->mode = "model";
    $module_info->class = $module_info->module."Model";
}else{
    $module_info->mode = "view";
    $module_info->class = $module_info->module."View";
}

if(isset($_GET['document_srl'])) $module_info->seq = $_GET['document_srl'];
$module_info->path = "./modules/".$module_info->module."/";
//
//echo "<!--//debug";
//print_r($module_info);
//echo "-->";

//라이브러리 로드
require_once ("./common.php");


//전달받은 파라미터들을 오브젝트화
$not_to_obj = array("success_return_url", "error_return_url");
$args = new stdClass();
foreach ($_POST as $key => $value) {
    if(in_array($key,$not_to_obj)) continue;
    $args->{$key} = $value;
    unset($_POST[$key]);
}



//템플릿 시작
$addHtmlHeader = array();
$addHtmlFooter = array();
$bodyclass = array();
$browser_title = false;

//로그인사용자 확인
$oMemberModel = getModel('member');
$logged_info = $oMemberModel->getLoggedInfo();
if($logged_info->error == -1) $logged_info = false;
else $logged_info = $logged_info->data;

//테마호출
if($module_info->is_admin == "Y"){
    $module_info->theme = "admin";
}else{
    $module_info->theme = "lgeprm";
}
$module_info->template_path = "./theme/". $module_info->theme . "/" . $module_info->module . "/";
$module_info->theme_path = "./theme/". $module_info->theme . "/";

//각종 라이브러리들을 절대경로로 호출하기 위해서 도메인을 설정한다.
//하지만 사이트의 설정이나 호출되는 페이지에따라 https로 domain변수를 변경할 수 있다.
$domain = "http://" . $_SERVER['HTTP_HOST'] . "/";
$current_url_clear = $domain;
if($module_info->module){
    $current_url_clear .= $module_info->module . "/";
    $module_url = $current_url_clear;
}
if($module_info->act){
    $current_url_clear .= $module_info->act . "/";
    $act_url = $current_url_clear;
}
if($module_info->seq){
    $current_url_clear .= $module_info->seq;
}
$current_url = $domain.$_SERVER['REQUEST_URI'];

//proc이면 함수를 실행하고, 대부분 함수내에서 redirect 하거나 뒤로 보냄.
//view면 함수실행 한 다음 템플릿파일 호출.
//run class
include_once "./modules/{$module_info->module}/{$module_info->mode}.php";
$object = new $module_info->class();
$function_name = $module_info->act;
$object->init();
$output = $object->$function_name($args);

//헤더,푸터는 전부 템플릿파일 내에서 호출하는걸로 변경 (레이아웃이 필요없는경우도 있어서)
if($_GET['returnType'] == "json"){
    echo json_encode($output);
    exit();
}else if($module_info->template_file){
    include $module_info->template_path . $module_info->template_file . ".html";
}else{
    include $module_info->theme_path . "message.html";
}

?>