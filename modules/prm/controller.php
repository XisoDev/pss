<?php

class prmController{
    function init(){

    }

    function procCreatePRM($args){
        //check grant
        global $logged_info;
        global $domain;
        //check variables
        if(!$args->dept_srl) return setReturn(-1, "사업부값이 누락되었습니다.");
        if(!$args->product_srl) return setReturn(-1, "제품유형이 누락되었습니다.");
        if(!$args->circu_srl) return setReturn(-1, "유통/법인정보가 누락되었습니다.");
        if(!$args->prm_title) return setReturn(-1, "PRM제목이 누락되었습니다.");

        //get Table ID
        $oProduct = sql_fetch("select * from `product` where `product_srl` = " . $args->product_srl);

        //circu_subs
        $circu_subs = explode("@",$args->circu_srl);

        //getMember
        $obj = new stdClass();
        $obj->prm_srl = getNextSequence();
        $obj->table_id = $oProduct['table_id'];
        $obj->member_srl = $logged_info->member_srl;
        $obj->product_srl = $oProduct['product_srl'];
        $obj->subs_srl = $circu_subs[1];
        $obj->circu_srl = $circu_subs[0];
        $obj->title = $args->prm_title;
        $obj->regdate = date("YmdHis");
        $obj->is_linked = "N";
        $obj->is_public = "N";

        $output = insertQuery("prm",$obj);
        if(!$output->result) return setReturn(-1,"PRM생성에 실패했습니다.");
        return setReturn(0, "created PRM", $domain . "prm/update/" . $obj->prm_srl);
    }

    function procUpdateDesign($args)
    {
        if(!$args->design_id) return setReturn(-1, "디자인그룹이 선택되지 않았습니다.");

        global $logged_info;
        global $module_info;

        //setPrm & Product Data
        $oPrmModel = getModel('prm');
        $output = new Object();
        $output->data = $oPrmModel->getPrm($module_info->seq);

        //check grant
        if($logged_info->member_srl != $output->data->member_srl) return setReturn(-1,"권한이 없습니다.");

        //선택된 그룹과 관련없는 모델들은 전부 제거해서 같이 수정해야함.

        //update query
        $obj = new stdClass();
        $obj->design_id = $args->design_id;
        $query = updateQueryString("prm",$obj);
        $query .= " where `prm_srl` = " . $output->data->prm_srl;
        sql_query($query);

        return setReturn(0, "success saved.");
    }

    function procUpdateModel($args){

        global $module_info;
        global $logged_info;

        $oPrmModel = getModel('prm');
        $output = new Object();
        $output->data = $oPrmModel->getPrm($module_info->seq);

        //check grant
        if($logged_info->member_srl != $output->data->member_srl) return setReturn(-1,"권한이 없습니다.");

        $obj = new stdClass();
        $obj->models = join(",",$args->model_cart);
        $query = updateQueryString("prm",$obj);
        $query .= " where `prm_srl` = " . $output->data->prm_srl;
        sql_query($query);

        return setReturn(0, "success saved.");
    }

    function procUpdateProfit($args){
        global $module_info;
        global $logged_info;

        $oPrmModel = getModel('prm');
        $output = new Object();
        $output->data = $oPrmModel->getPrm($module_info->seq);

        //check grant
        if($logged_info->member_srl != $output->data->member_srl) return setReturn(-1,"권한이 없습니다.");

        $obj = new stdClass();
        $obj->profit = json_decode($args->profit);
        $obj->profit = serialize($obj->profit);

        $query = updateQueryString("prm",$obj);
        $query .= " where `prm_srl` = " . $output->data->prm_srl;
        sql_query($query);

        return setReturn(0, "success saved.");
    }

    function procUpdateResult($args){
        global $module_info;
        global $logged_info;

        $oPrmModel = getModel('prm');
        $output = new Object();
        $output->data = $oPrmModel->getPrm($module_info->seq);

        //check grant
        if($logged_info->member_srl != $output->data->member_srl) return setReturn(-1,"권한이 없습니다.");

        $obj = new stdClass();
        $obj->result = json_decode($args->result);
        $obj->result = serialize($obj->result);

        $query = updateQueryString("prm",$obj);
        $query .= " where `prm_srl` = " . $output->data->prm_srl;
        sql_query($query);

        return setReturn(0, "success saved.");
    }
}