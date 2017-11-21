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

        //all list
        $query = "select * from `currency`";
        $output = sql_query_array($query);

        //used list
        // subs group by cureency
        $used_currency = "SELECT `currency`.* FROM  `subs` left join `currency` on `currency`.`code` = `subs`.`currency` group by `subs`.`currency`";
        $used_output = sql_query_array($used_currency);

        $output->used = $used_output;

        // prod group by cureency
        $prod_currency = "SELECT `currency`.* FROM  `proc_fees` left join `currency` on `currency`.`code` = `proc_fees`.`prod_currency` group by `proc_fees`.`prod_currency`";
        $prod_output = sql_query_array($prod_currency);

        $output->used = $used_output;
        $output->prod = $prod_output;

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

        $output = new stdClass();
        $output->oMember = new stdClass();

        if($module_info->seq){
            $output->title_message = "Update Member Info";
            $output->button_message = "Update Apply";
            //getMemberInfo
            $oMemberModel = getModel('member');
            $output->oMember = $oMemberModel->getMemberInfoByMemberSrl($module_info->seq)->data;
        }

        if(!$output->oMember->member_srl){
            $output->title_message = "Create New Member";
            $output->button_message = "Sign Up";
        }
        //circu list
        $query = "select * from `circu` left join `subs` on `subs`.`subs_srl` = `circu`.`subs_srl` order by `circu`.`subs_srl` ASC";
        $temp_circu_list = sql_query_array($query)->data;
        $output->circu_list = array();
        foreach($temp_circu_list as $circu_info){
            $output->circu_list[$circu_info->circu_srl . "@" . $circu_info->subs_srl] = $circu_info;
        }

        //사업부별 제품유형 get
        $sql = "SELECT * FROM  `dept` ";
        $temp = sql_query_array($sql)->data;
        $output->dept_list = array();

        $temp3 = new stdClass();
        $temp3->type_srl = false;
        $temp3->type_title = "Not Permitted";
        foreach($temp as $dept){
            $output->dept_list[$dept->dept_srl] = $dept;
            $temp2 = sql_query_array("SELECT * FROM `product` where `dept_srl` = {$dept->dept_srl}")->data;
            if(!count($temp2)) continue;
            foreach($temp2 as $product){
                $product->permissions = sql_query_array("SELECT * FROM `member_type` where `product_srl` = {$product->product_srl}")->data;
                array_unshift($product->permissions, $temp3);

                $output->dept_list[$dept->dept_srl]->product_list[$product->product_srl] = $product;
            }
        }
        unset($temp,$temp2,$temp3);

        $module_info->template_file = "member_insert";
        return $output;
    }
//회원 권한설정
    function dispMemberPermission($args){
        global $module_info;
        $output = new stdClass();

        //product_type list
        $query = "select `product`.*, `dept`.`dept_title` from `product` left join `dept` on `dept`.`dept_srl` = `product`.`dept_srl` order by `product`.`dept_srl` ASC";
        $productObj = sql_query_array($query);
        $output->product = $productObj->data;
        
        //permission list
        $permissions = new stdClass();
        $permissions->prm = array();
        $permissions->prm["design_group"] = "디자인 그룹선택";
        $permissions->prm["model"] = "모델 선정";
        $permissions->prm["stepup"] = "스탭업로직 적용";
        $permissions->prm["profit"] = "수익성 분석";
        $permissions->prm["result"] = "동일법인 내 유통분석";
        $permissions->prm["spec"] = "스펙 및 마컴물 열람";

        $permissions->function = array();
        $permissions->function["tocopy"] = "생성된 PRM 복사하여 공유";
        $permissions->function["public"] = "생성된 PRM을 대표 PRM으로 출판";
        $output->permissions = $permissions;

        //type list
        $output->type_list = sql_query_array("SELECT `member_type`.*, `product`.`product_title` FROM `member_type` left join `product` on `member_type`.`product_srl` = `product`.`product_srl` order by `member_type`.`product_srl`")->data;

        //set UpdateInfo
        if($module_info->seq){
            //setMessage
            $output->oType = sql_fetch("select * from `member_type` where `type_srl` = {$module_info->seq}");
            $output->oType['permission'] = unserialize($output->oType['permission']);
            $output->title_message = "update Permission";
            $output->button_message = "Update";
        }else{
            $output->oType = array();
            //setMessage
            $output->title_message = "Create New Permission";
            $output->button_message = "Create";
        }


        $module_info->template_file = "member_permission";
        return $output;
    }
//법인
    function dispSubsDiary(){
        global $module_info;

        //getSubsList
        $query = "select * from `subs` ORDER BY  `subs`.`region` ASC, `subs`.`subs_title` ASC";
        $output = sql_query_array($query);

        $output->currency_list = sql_query_array("select * from `currency`")->data;

        $module_info->template_file = "subsdiary";
        return $output;
    }

//유통
    function dispSubsCircurator(){
        global $module_info;


        //getSubsList
        $query = "select * from `circu` left join `subs` on `subs`.`subs_srl` = `circu`.`subs_srl`";
        $output = sql_query_array($query);

        //getSubsList
        $query = "select * from `subs` ORDER BY  `subs`.`region` ASC, `subs`.`subs_title` ASC";
        $output->subs = sql_query_array($query);

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
            $oProduct->macom_list = unserialize($oProduct->macom_list);
            $oProduct->stepup_logic = unserialize($oProduct->stepup_logic);
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

        }else if($_GET['tab'] == 'macom'){

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
        }else if($_GET['tab'] == 'design'){
            $ex_sql = sprintf("select `design_ex_img` from `product_%s` group by `design_ex_img`",$oProduct->table_id);
            $ex_list = sql_query_array($ex_sql);

            $in_sql = sprintf("select `design_in_img` from `product_%s` group by `design_in_img`",$oProduct->table_id);
            $in_list = sql_query_array($in_sql);

            $output->ex_list = array();
            foreach($ex_list->data as $item){
                $output->ex_list[] = $item->design_ex_img;
            }

            $output->in_list = array();
            foreach($in_list->data as $item){
                $output->in_list[] = $item->design_in_img;
            }
        }else if($_GET['tab'] == 'option'){
            //해당 product의 matrix중, model에 use된 field 리스트를 group by 해서 가져옴.

            $table_id = $oProduct->table_id;

            $model_fields = array();
            $oSettingController = getController('settings');
            foreach($oProduct->field_model as $field => $info){
                if($info['model_use'] != "Y") continue;
                $model_fields[$info['model_order']] = $info;
                $model_fields[$info['model_order']]['field'] = $field;
                $model_fields[$info['model_order']]['th'] = $output->data->field_th[$field];

                $query = sprintf("select %s from product_%s group by %s",$field,$table_id,$field);
                $list = sql_query_array($query);

                $model_fields[$info['model_order']]['list'] = array();
                foreach($list->data as $val){
                    $val = $oSettingController->replaceField($val->{$field},true);
                    $model_fields[$info['model_order']]['list'][] = $val;
                }
            }
            ksort($model_fields);
            $output->model_fields = $model_fields;

            //get Options
            $options = sql_query_array("SELECT * FROM  `options` where `table_id` = '{$table_id}'");
            $output->options = array();
            foreach($options->data as $option){
                if(!is_array($output->options[$option->field])) $output->options[$option->field] = array();
                $output->options[$option->field][$option->record] = $option;
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