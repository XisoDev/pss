<?php

class menuModel{
    function init(){

    }

    function getMenu($menu_srl){
        global $module_info, $domain;
        $menus = array();

        if($menu_srl == "prm"){
            //HOME
            $item = new stdClass();
            $item->title = "HOME";
            $item->link = $domain;
            $item->active = ($module_info->act == "index" && $module_info->module == "public") ? true : false;
            $item->icon_color = "text-aqua";
            $item->icon = "fa-home";
            $item->new_window = "N";
            $item->children = array();
            $menus[] = $item;

            //PRM
            $item = new stdClass();
            $item->title = "PRM";
            $item->link = $domain . "prm";
            $item->active = ($module_info->module == "prm") ? true : false;
            $item->icon_color = "text-red";
            $item->icon = "fa-map";
            $item->new_window = "N";
            $item->children = array();
                $subitem = new stdClass();
                $subitem->title = "List";
                $subitem->link = $domain . "prm";
                $subitem->active = ($module_info->module == "prm") ? true : false;
                $subitem->icon = "fa-list";
                $subitem->icon_color = false;
                $subitem->new_window = "N";
                $item->children[] = $subitem;

                $subitem = new stdClass();
                $subitem->title = "Create";
                $subitem->link = $domain . "prm/create";
                $subitem->active = ($module_info->module == "prm" && $module_info->act == "create") ? true : false;
                $subitem->icon = "fa-pencil-square-o";
                $subitem->icon_color = false;
                $subitem->new_window = "N";
                $item->children[] = $subitem;
            $menus[] = $item;

            //Subsidiary
            $item = new stdClass();
            $item->title = "Subsidiary";
            $item->link = $domain;
            $item->active = ($module_info->act == "index" && $module_info->module == "subsidiary") ? true : false;
            $item->icon_color = "text-yellow";
            $item->icon = "fa-building-o";
            $item->new_window = "N";
            $item->children = array();
            $menus[] = $item;

        }else if($menu_srl == "settings"){
            //HOME
            $item = new stdClass();
            $item->title = "대시보드";
            $item->link = $domain;
            $item->active = ($module_info->act == "index" && $module_info->module == "settings") ? true : false;
            $item->icon = "fa-dashboard";
            $item->new_window = "N";
            $item->children = array();
            $menus[] = $item;

            //HOME
            $item = new stdClass();
            $item->title = "회원관리";
            $item->link = $domain;
            $item->active = (strpos($module_info->act,"Member") && $module_info->module == "settings") ? true : false;
            $item->icon = "fa-user-circle-o";
            $item->new_window = "N";
            $item->children = array();
                $subitem = new stdClass();
                $subitem->title = "회원목록";
                $subitem->link = $domain . "settings/dispMemberList";
                $subitem->active = ($module_info->module == "settings" && $module_info->act == "dispMemberList") ? true : false;
                $subitem->icon = "fa-users";
                $subitem->icon_color = false;
                $subitem->new_window = "N";
                $item->children[] = $subitem;

                $subitem = new stdClass();
                $subitem->title = "회원추가";
                $subitem->link = $domain . "settings/dispMemberCreate";
                $subitem->active = ($module_info->module == "settings" && $module_info->act == "dispMemberCreate") ? true : false;
                $subitem->icon = "fa-user-plus";
                $subitem->icon_color = false;
                $subitem->new_window = "N";
                $item->children[] = $subitem;

                $subitem = new stdClass();
                $subitem->title = "회원유형설정";
                $subitem->link = $domain . "settings/dispMemberPermission";
                $subitem->active = ($module_info->module == "settings" && $module_info->act == "dispMemberPermission") ? true : false;
                $subitem->icon = "fa-unlock-alt";
                $subitem->icon_color = false;
                $subitem->new_window = "N";
                $item->children[] = $subitem;
            $menus[] = $item;

            //Subsdiary setting
            $item = new stdClass();
            $item->title = "법인/유통";
            $item->link = $domain;
            $item->active = (strpos($module_info->act,"Subs") && $module_info->module == "settings") ? true : false;
            $item->icon = "fa-building";
            $item->new_window = "N";
            $item->children = array();
                $subitem = new stdClass();
                $subitem->title = "통화관리";
                $subitem->link = $domain . "settings/dispSubsCurrency";
                $subitem->active = ($module_info->module == "settings" && $module_info->act == "dispSubsCurrency") ? true : false;
                $subitem->icon = "fa-usd";
                $subitem->icon_color = false;
                $subitem->new_window = "N";
                $item->children[] = $subitem;

//                $subitem = new stdClass();
//                $subitem->title = "지역생성";
//                $subitem->link = $domain . "settings/dispSubsRegion";
//                $subitem->active = ($module_info->module == "settings" && $module_info->act == "dispSubsRegion") ? true : false;
//                $subitem->icon = "fa-map-marker";
//                $subitem->icon_color = false;
//                $subitem->new_window = "N";
//                $item->children[] = $subitem;

                $subitem = new stdClass();
                $subitem->title = "법인 관리";
                $subitem->link = $domain . "settings/dispSubsDiary";
                $subitem->active = ($module_info->module == "settings" && $module_info->act == "dispSubsDiary") ? true : false;
                $subitem->icon = "fa-globe";
                $subitem->icon_color = false;
                $subitem->new_window = "N";
                $item->children[] = $subitem;

                $subitem = new stdClass();
                $subitem->title = "유통관리";
                $subitem->link = $domain . "settings/dispSubsCircurator";
                $subitem->active = ($module_info->module == "settings" && $module_info->act == "dispSubsCircurator") ? true : false;
                $subitem->icon = "fa-truck";
                $subitem->icon_color = false;
                $subitem->new_window = "N";
                $item->children[] = $subitem;
            $menus[] = $item;

            //Subsdiary setting
            $item = new stdClass();
            $item->title = "사업부 관리";
            $item->link = $domain . "settings/dispDept";
            $item->active = ($module_info->module == "settings" && $module_info->act == "dispDept") ? true : false;
            $item->icon = "fa-clipboard";
            $item->new_window = "N";
            $item->children = array();
            $menus[] = $item;

            $item = new stdClass();
            $item->title = "제품관리";
            $item->link = $domain . "settings/dispProduct";
            $item->active = ($module_info->module == "settings" && strpos($module_info->act,"Product")) ? true : false;
            $item->icon = "fa-archive";
            $item->icon_color = false;
            $item->new_window = "N";
            $item->children = array();
                $subitem = new stdClass();
                $subitem->title = "제품유형 추가";
                $subitem->link = $domain . "settings/dispProductType";
                $subitem->active = ($module_info->module == "settings" && $module_info->act == "dispProductType") ? true : false;
                $subitem->icon = "fa-plus";
                $subitem->icon_color = 'text-red';
                $subitem->new_window = "N";
                $item->children[] = $subitem;

                $subitem = new stdClass();
                $subitem->title = "제품유형별 관리";
                $subitem->link = $domain . "settings/dispProductTypeList";
                $subitem->active = ($module_info->module == "settings" && $module_info->act == "dispProductTypeList") ? true : false;
                $subitem->icon = "fa-barcode";
                $subitem->icon_color = false;
                $subitem->new_window = "N";
                $item->children[] = $subitem;
            $menus[] = $item;

            //Subsdiary setting
            $item = new stdClass();
            $item->title = "데이터센터";
            $item->link = $domain . "settings/dispDatacenter";
            $item->active = ($module_info->module == "settings" && $module_info->act == "dispDatacenter") ? true : false;
            $item->icon = "fa-database";
            $item->new_window = "N";
            $item->children = array();
            $menus[] = $item;
        }

        return $menus;
    }
}