<?php

class prmView{
    function init(){

    }

    function index(){
        global $module_info;

        //setPrm & Product Data
        $oPrmModel = getModel('prm');
        $output = sql_query_array("select * from `prm` limit 0,30");

        //arange data
        foreach($output->data as $key => $prm){
             $output->data[$key] = $oPrmModel->getPrm($prm->prm_srl);
        }

        $module_info->template_file = "index";
        return $output;
    }

    function create(){
        global $module_info;
        $output = new Object();

        //set dept list
        $query = "select * from `dept`";
        $deptObj = sql_query_array($query);
        $output->dept = $deptObj->data;

        $module_info->template_file = "create";
        return $output;
    }

    function update(){
        global $module_info;
        global $logged_info;
        global $domain;

        $returnUrl = $domain . "prm";
        if(!$module_info->seq) return setReturn(-1, "수정할 PRM을 선택해주시기 바랍니다.", $returnUrl);

        //setPrm & Product Data
        $oPrmModel = getModel('prm');
        $output = new Object();
        $output->data = $oPrmModel->getPrm($module_info->seq);

        //check grant
        if($logged_info->member_srl != $output->data->member_srl) return setReturn(-1,"권한이 없습니다.", $returnUrl);

        $module_info->template_file = "update";
        return $output;
    }
}
?>