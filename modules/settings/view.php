<?php

class settingsView {

    //    공통 실행
    function init(){
        global $domain;
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

        //관리자가 아니면 튕굼
        if($logged_info->data->is_admin != "Y"){
            $current_url = $domain;
            header('Location: '.$current_url);
        }
    }

//    첫페이지.
    function index(){
        global $module_info;

        $module_info->template_file = "index";
    }

    function dispSubsCurrency(){
        global $module_info;

        $query = "select * from `currency`";
        $output = sql_query_array($query);

        $module_info->template_file = "currency";
        return $output;
    }

//    회원관리
    function dispMemberList(){
        global $module_info;
        //search condition

        //pagenation

        //list
        $query = "select * from `member` limit 0,30";
        $output = sql_query_array($query);

        $module_info->template_file = "member_list";
        return $output;
    }
//회원 추가/수정
    function dispMemberCreate($args){
        global $module_info;

        //getMemberInfo
        $oMemberModel = getModel('member');
        $output = $oMemberModel->getMemberInfoByMemberSrl($module_info->seq);

        $module_info->template_file = "member_insert";
        return $output;
    }
//법인
    function dispSubsDiary(){
        global $module_info;

        //getSubsList
        $query = "select * from `subs`";
        $output = sql_query_array($query);

        $module_info->template_file = "subsdiary";
        return $output;
    }

//유통
    function dispSubsCircurator(){
        global $module_info;

        //getSubsList
        $query = "select * from `circu`";
        $output = sql_query_array($query);

        $module_info->template_file = "circurator";
        return $output;
    }

//    사업부
    function dispDept(){
        global $module_info;

        //getDepts
        $query = "select * from `dept`";
        $output = sql_query_array($query);

        $module_info->template_file = "dept";
        return $output;
    }

//    대망의 제품유형관리
    function dispProductType(){
        global $module_info;

        $query = "select * from `dept`";
        $output = sql_query_array($query);

        $module_info->template_file = "product_add";
        return $output;
    }

    function dispProductTypeList(){
        global $module_info;
        global $current_url_clear;

        $output = new Object();

        //getDepts
        $query = "select * from `dept`";
        $deptObj = sql_query_array($query);
        $output->dept = $deptObj->data;

        if($_GET['dept_srl']){
            $query = "select * from `product` where `dept_srl` = '{$_GET['dept_srl']}'";
            $productObj = sql_query_array($query);
            $output->product = $productObj->data;
        }

        if($module_info->seq){
            $query = "select * from `product` where `product_srl` = ". $module_info->seq;
            $oProduct = (object) sql_fetch($query);

            if(!isset($_GET['dept_srl'])){
                $url = $current_url_clear . "?dept_srl=" . $oProduct->dept_srl;
                if(isset($_GET['tab'])) $url .= "&tab=" . $_GET['tab'];

                header('Location: ' . $url);
            }
            $oProduct->field_th = unserialize($oProduct->field_th);
            $oProduct->field_model = unserialize($oProduct->field_model);
            $oProduct->field_spec = unserialize($oProduct->field_spec);
            $oProduct->field_info = unserialize($oProduct->field_info);
            $oProduct->design_group = unserialize($oProduct->design_group);
            $output->oProduct = $oProduct;
        }

        //set Circurator List
        if(!isset($_GET['tab']) && isset($oProduct)){
            $query = "SELECT `circu`.`circu_srl`, `circu`.`circu_title`, `circu`.`circu_title_abb`, `subs`.`subs_srl`, `subs`.`subs_title`, `subs`.`region`, `subs`.`currency` FROM `circu` left join `subs` on `subs`.`subs_srl` = `circu`.`subs_srl`";
            $query .= " order by `subs`.`subs_title` ASC, `circu`.`circu_title` ASC";
            $circu_list = sql_query_array($query)->data;
            $output->circu_list = array();
            foreach($circu_list as $circu){
                $output->circu_list[$circu->circu_srl] = $circu;
            }

            $query = "select * from `circu_product` where `product_srl` = " . $oProduct->product_srl;
            $output->oProduct->circu_list = sql_query_array($query)->data;
        }else if($_GET['tab'] == "salefees"){
            //get subslist
            $query = "SELECT * FROM `circu_product` left join `subs` on `circu_product`.`subs_srl` = `subs`.`subs_srl` where `product_srl` = {$oProduct->product_srl} group by `circu_product`.`subs_srl` ";
            $output->subs_list = sql_query_array($query)->data;

            $settingsModel = &getModel('settings');

            //get category_list
            $output->category_list = $settingsModel->getCategoryList($oProduct->table_id);

            //set FieldList
            $output->sales_fields = $settingsModel->getSalesfeesField();

            //set Values
            if($_GET['subs_srl']){
                $data = sql_query_array(sprintf("select * FROM `sale_fees` where `product_srl` = %d and `subs_srl` = %d",$oProduct->product_srl,$_GET['subs_srl']));
                foreach($data->data as $fees){
                    if(!$output->sale_values[$fees->category])
                        $output->sale_values[$fees->category] = array();
                    $output->sale_values[$fees->category] = $fees;
                }
                unset($data);
            }
        }else if($_GET['tab'] == "designgroup"){

        }else if($_GET['tab'] == "procfees"){
            //get subslist
            $query = "SELECT `prod_subs` FROM `product_".$oProduct->table_id."` group by `prod_subs`";
            $data = sql_query_array($query)->data;
            foreach($data as $prod_subs){
                $output->prod_subs[] = $prod_subs->prod_subs;
            }
            unset($data);


            $settingsModel = &getModel('settings');

            //get category_list
            $output->category_list = $settingsModel->getCategoryList($oProduct->table_id);

            //get currency list
            $output->currency_list = sql_query_array("select * from `currency`")->data;

            //set FieldList
            $output->proc_fields = $settingsModel->getProcfeesField();

            //set Values
            if($_GET['prod_subs']){
                $data = sql_query_array(sprintf("select * FROM `proc_fees` where `product_srl` = %d and `prod_subs` = '%s'",$oProduct->product_srl,$_GET['prod_subs']));
                foreach($data->data as $fees){
                    if(!$output->proc_values[$fees->category])
                        $output->proc_values[$fees->category] = array();
                    $output->proc_values[$fees->category] = $fees;
                    $output->prod_currency = $fees->prod_currency;
                }
                unset($data);
            }
        }else if($_GET['tab'] == 'settingfield'){

            //arrange FieldList
            $field_list = array();
            foreach($oProduct->field_th as $field => $th){
                if(!isset($field_list[$field])) $field_list[$field] = new stdClass();
                $field_list[$field]->th = $th;
                $field_list[$field]->field = $field;
//                model
                $field_list[$field]->model_display = $oProduct->field_model[$field]['model_display'];
                $field_list[$field]->model_use = $oProduct->field_model[$field]['model_use'];
                $field_list[$field]->model_order = $oProduct->field_model[$field]['model_order'];
//                spec
                $field_list[$field]->spec_display = $oProduct->field_spec[$field]['spec_display'];
                $field_list[$field]->spec_use = $oProduct->field_spec[$field]['spec_use'];
                $field_list[$field]->spec_header = $oProduct->field_spec[$field]['spec_header'];
                $field_list[$field]->spec_order = $oProduct->field_spec[$field]['spec_order'];
//                spec
                $field_list[$field]->info_use = $oProduct->field_info[$field]['info_use'];
                $field_list[$field]->info_order = $oProduct->field_info[$field]['info_order'];
            }

            $output->field_list = $field_list;
        }

        $module_info->template_file = "product_list";
        return $output;
    }
}

?>