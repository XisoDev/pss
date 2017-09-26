<?php
/*******************************************************************************
** 공통 변수, 상수, 코드
*******************************************************************************/
error_reporting( E_CORE_ERROR | E_CORE_WARNING | E_COMPILE_ERROR | E_ERROR | E_WARNING | E_PARSE | E_USER_ERROR | E_USER_WARNING );

// 보안설정이나 프레임이 달라도 쿠키가 통하도록 설정
header('P3P: CP="ALL CURa ADMa DEVa TAIa OUR BUS IND PHY ONL UNI PUR FIN COM NAV INT DEM CNT STA POL HEA PRE LOC OTC"');

//==========================================================================================================================
// extract($_GET); 명령으로 인해 page.php?_POST[var1]=data1&_POST[var2]=data2 와 같은 코드가 _POST 변수로 사용되는 것을 막음
// 081029 : letsgolee 님께서 도움 주셨습니다.
//--------------------------------------------------------------------------------------------------------------------------
$ext_arr = array ('PHP_SELF', '_ENV', '_GET', '_POST', '_FILES', '_SERVER', '_COOKIE', '_SESSION', '_REQUEST',
                  'HTTP_ENV_VARS', 'HTTP_GET_VARS', 'HTTP_POST_VARS', 'HTTP_POST_FILES', 'HTTP_SERVER_VARS',
                  'HTTP_COOKIE_VARS', 'HTTP_SESSION_VARS', 'GLOBALS');
$ext_cnt = count($ext_arr);
for ($i=0; $i<$ext_cnt; $i++) {
    // POST, GET 으로 선언된 전역변수가 있다면 unset() 시킴
    if (isset($_GET[$ext_arr[$i]]))  unset($_GET[$ext_arr[$i]]);
    if (isset($_POST[$ext_arr[$i]])) unset($_POST[$ext_arr[$i]]);
}
//==========================================================================================================================



define("_XISO_", TRUE);
@session_start();

if (!defined('_XISO_PATH_')){
    define('_XISO_PATH_', str_replace('common.php', '', str_replace('\\', '/', __FILE__)));
}


if($_SESSION['_XISO_ERROR_']){
    $_XISO_ERROR_ = $_SESSION['_XISO_ERROR_'];
    unset($_SESSION['_XISO_ERROR_']);
}

if($_SESSION['_XISO_MESSAGE_']){
    $_XISO_MESSAGE_ = $_SESSION['_XISO_MESSAGE_'];
    unset($_SESSION['_XISO_MESSAGE_']);
}

require_once _XISO_PATH_."inc/db.config.php";
require_once _XISO_PATH_."inc/func.inc.php";
require_once _XISO_PATH_."inc/Object.class.php";
require_once _XISO_PATH_."inc/PageHandler.class.php";

require_once _XISO_PATH_."inc/file/FileObject.class.php";
require_once _XISO_PATH_."inc/file/FileHandler.class.php";
require_once _XISO_PATH_."inc/security/Password.class.php";



?>