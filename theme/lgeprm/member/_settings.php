<?php

$addHtmlHeader[] = "<link rel=\"stylesheet\" href=\"{$domain}{$module_info->template_path}css/login.css\">";
$addHtmlHeader[] = "<link rel=\"stylesheet\" href=\"{$domain}{$module_info->theme_path}css/toastem.css\">";
$addHtmlHeader[] = '<link rel="stylesheet" href="'.$domain.$module_info->theme_path.'assets/bower_components/font-awesome/css/font-awesome.min.css">';

$addHtmlFooter[] = '<script src="'.$domain.$module_info->theme_path.'assets/bower_components/jquery/dist/jquery.min.js"></script>';
$addHtmlFooter[] = "<script src=\"{$domain}{$module_info->template_path}js/login.js\"></script>";
$addHtmlFooter[] = "<script src=\"{$domain}{$module_info->theme_path}js/toastem.js\"></script>";

?>