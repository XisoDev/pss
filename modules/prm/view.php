<?php

class prmView{
    function init(){

    }

    function index(){
        global $module_info;
        global $logged_info;


        //search conditions
        $search_conditions = new stdClass();
        //set dept list
        $query = "select * from `dept`";
        $search_conditions->dept = sql_query_array($query)->data;

        //product type
        $query = "select * from `product`";
        if($_GET['search_dept']){
            $query .= " where `dept_srl` = " . $_GET['search_dept'];
        }
        $search_conditions->product_type = sql_query_array($query)->data;

        //subs list
        if($_GET["search_product"]){
            $query = "select * from `circu_product` left join `subs` on `circu_product`.`subs_srl` = `subs`.`subs_srl` where `circu_product`.`product_srl` = " . $_GET["search_product"] . " group by `circu_product`.`subs_srl`";
        }else{
            $query = "select * from `subs`";
        }
        $search_conditions->subs = sql_query_array($query)->data;

        //setPrm & Product Data
        $oPrmModel = getModel('prm');
        $oMemberModel = getModel('member');
        $query = "select `prm`.*, `product`.`dept_srl` from `prm` left join `product` on `prm`.`product_srl` = `product`.`product_srl` where ";

        $where = array();

        if($_GET['search_dept']){
            $val = addslashes($_GET['search_dept']);
            $where[] = "`product`.`dept_srl` = " . $val;
        }
        if($_GET['search_product']){
            $val = addslashes($_GET['search_product']);
            $where[] = "`prm`.`product_srl` = " . $val;
        }
        if($_GET['search_subs']){
            $val = addslashes($_GET['search_subs']);
            $where[] = "`prm`.`subs_srl` = " . $val;
        }
        if($_GET['search_keyword']){
            $val = addslashes($_GET['search_keyword']);
            $where[] = "`prm`.`title` = like '%" . $_GET['search_keyword'] . "%'";
        }

        if(!count($where)){
            $where = "1";
        }else{
            $where = join(" and ",$where);
        }

        $query .= $where . " limit 0,30";
        $output = sql_query_array($query);

        //arange data
        foreach($output->data as $key => $prm){
            $output->data[$key] = $oPrmModel->getPrm($prm->prm_srl);
            $output->data[$key]->permission = $oMemberModel->getPermission($logged_info->permissions[$prm->product_srl])->permission;
        }

        //added variables
        $output->search_conditions = $search_conditions;

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

        //getPermission
        $oMemberModel = getModel('member');
        $permission = $oMemberModel->getPermission($logged_info->permissions[$output->data->product_srl])->permission;

        //set StepList
        $step_list = array();
        if($output->data->design_group && $permission["prm"]["design_group"] == "Y"){
            $step_list[] = "Design Identity";
        }

        if($permission["prm"]["model"] == "Y"){
            $step_list[] = "Select Models";
        }
        
        if(count($output->data->stepup_logic) && $permission["prm"]["stepup"] == "Y"){
            $step_list[] = "Step-Up Logic";
        }

        if($permission["prm"]["profit"] == "Y"){
            $step_list[] = "Profitability";
        }

        if($permission["prm"]["result"] == "Y"){
            $step_list[] = "PRM Result";
        }

        if($permission["prm"]["spec"] == "Y"){
            $step_list[] = "Macom & Spec";
        }

        $output->step_list = $step_list;

        $module_info->template_file = "update";
        return $output;
    }
}
?>