<?php
$addHtmlHeader[] = "<link rel='stylesheet' type='text/css' href='{$domain}{$module_info->theme_path}/css/reset.css'>";



$addHtmlHeader[] = '<link rel="stylesheet" href="'.$domain.$module_info->theme_path.'assets/bower_components/bootstrap/dist/css/bootstrap.min.css">';
$addHtmlHeader[] = '<link rel="stylesheet" href="'.$domain.$module_info->theme_path.'assets/bower_components/font-awesome/css/font-awesome.min.css">';
$addHtmlHeader[] = '<link rel="stylesheet" href="'.$domain.$module_info->theme_path.'assets/bower_components/Ionicons/css/ionicons.min.css">';
$addHtmlHeader[] = '<link rel="stylesheet" href="'.$domain.$module_info->theme_path.'assets/dist/css/AdminLTE.css">';
$addHtmlHeader[] = '<link rel="stylesheet" href="'.$domain.$module_info->theme_path.'assets/dist/css/skins/skin-lgeprm.css">';
$addHtmlHeader[] = '<link rel="stylesheet" href="'.$domain.$module_info->theme_path.'assets/bower_components/morris.js/morris.css">';
$addHtmlHeader[] = '<link rel="stylesheet" href="'.$domain.$module_info->theme_path.'assets/bower_components/jvectormap/jquery-jvectormap.css">';
$addHtmlHeader[] = '<link rel="stylesheet" href="'.$domain.$module_info->theme_path.'assets/bower_components/bootstrap-datepicker/dist/css/bootstrap-datepicker.min.css">';
$addHtmlHeader[] = '<link rel="stylesheet" href="'.$domain.$module_info->theme_path.'assets/bower_components/bootstrap-daterangepicker/daterangepicker.css">';
$addHtmlHeader[] = '<link rel="stylesheet" href="'.$domain.$module_info->theme_path.'assets/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css">';
$addHtmlHeader[] = '<link rel="stylesheet" href="'.$domain.$module_info->theme_path.'css/toastem.css">';


$addHtmlHeader[] = '<script src="'.$domain.$module_info->theme_path.'assets/bower_components/jquery/dist/jquery.min.js"></script>';
$addHtmlHeader[] = '<script src="'.$domain.$module_info->theme_path.'assets/bower_components/jquery-ui/jquery-ui.min.js"></script>';

$addHtmlFooter[] = "<script>$.widget.bridge('uibutton', $.ui.button);</script>";
$addHtmlFooter[] = '<script src="'.$domain.$module_info->theme_path.'assets//bower_components/bootstrap/dist/js/bootstrap.min.js"></script>';
$addHtmlFooter[] = '<script src="'.$domain.$module_info->theme_path.'assets//bower_components/raphael/raphael.min.js"></script>';
$addHtmlFooter[] = '<script src="'.$domain.$module_info->theme_path.'assets//bower_components/morris.js/morris.min.js"></script>';
$addHtmlFooter[] = '<script src="'.$domain.$module_info->theme_path.'assets//bower_components/jquery-sparkline/dist/jquery.sparkline.min.js"></script>';
//$addHtmlFooter[] = '<script src="'.$domain.$module_info->theme_path.'assets//plugins/jvectormap/jquery-jvectormap-1.2.2.min.js"></script>';
//$addHtmlFooter[] = '<script src="'.$domain.$module_info->theme_path.'assets//plugins/jvectormap/jquery-jvectormap-world-mill-en.js"></script>';
$addHtmlFooter[] = '<script src="'.$domain.$module_info->theme_path.'assets//bower_components/jquery-knob/dist/jquery.knob.min.js"></script>';
$addHtmlFooter[] = '<script src="'.$domain.$module_info->theme_path.'assets//bower_components/moment/min/moment.min.js"></script>';
$addHtmlFooter[] = '<script src="'.$domain.$module_info->theme_path.'assets//bower_components/bootstrap-daterangepicker/daterangepicker.js"></script>';
$addHtmlFooter[] = '<script src="'.$domain.$module_info->theme_path.'assets//bower_components/bootstrap-datepicker/dist/js/bootstrap-datepicker.min.js"></script>';
$addHtmlFooter[] = '<script src="'.$domain.$module_info->theme_path.'assets//plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js"></script>';
$addHtmlFooter[] = '<script src="'.$domain.$module_info->theme_path.'assets//bower_components/jquery-slimscroll/jquery.slimscroll.min.js"></script>';
$addHtmlFooter[] = '<script src="'.$domain.$module_info->theme_path.'assets//bower_components/fastclick/lib/fastclick.js"></script>';
$addHtmlFooter[] = '<script src="'.$domain.$module_info->theme_path.'assets//dist/js/adminlte.min.js"></script>';
$addHtmlFooter[] = '<script src="'.$domain.$module_info->theme_path.'js/toastem.js"></script>';
//$addHtmlFooter[] = '<script src="'.$domain.$module_info->theme_path.'assets//dist/js/pages/dashboard.js"></script>';
//$addHtmlFooter[] = '<script src="'.$domain.$module_info->theme_path.'assets//dist/js/demo.js"></script>';

$bodyclass[] = "hold-transition";
$bodyclass[] = "skin-lgeprm";
$bodyclass[] = "sidebar-mini";
//$bodyclass[] = "sidebar-collapse";

function loadHeader($title, $subtitle = false, $breadcrumb = array()){
    global $domain;
    global $module_info;
    echo "<section class=\"content-header\">";
    echo "<h1>" . $title;
        if($subtitle) echo "<small>{$subtitle}</small>";
        echo "</h1>";

    echo "<ol class=\"breadcrumb\">";
        echo "<li><a href=\"{$domain}{$module_info->module}\"";
        if($module_info->act == "index") echo "class='active'";
        echo "><i class=\"fa fa-home\"></i> Home</a></li>";


        echo "<li class=\"active\">" . $title . "</li>";
        echo "</ol></section>";
}

?>