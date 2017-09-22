<?php

class settingsController{
    function init(){

    }

//사업부 생성
    function procDeptInsert($args){
        global $module_info;
        $mode = false;
        $message = "업데이트 되었습니다.";
        if(!$args->dept_srl){
            if($module_info->seq){
                $args->dept_srl = $module_info->seq;
                $mode = "update";
            }else{
                $args->dept_srl = getNextSequence();
                $args->list_order = $args->dept_srl * -1;
                $args->regdate = date("YmdHis");
                $mode = "insert";
                $message = "사업부가 생성되었습니다.";
            }
        }else{
            $mode = "update";
        }

        if($mode == "update"){
            $where = " where `dept_srl` = " . $args->dept_srl;
            unset($args->dept_srl);

            $query = updateQueryString("dept",$args);
            $query .= $where;

            $output = new Object();
            $output->result = sql_query($query);
        }else{
            $output = insertQuery("dept",$args);
        }

        if($output->result){
            return setReturn(0, $message);
        }else{
            return setReturn(-1, "사업부 생성에 실패하였습니다.");
        }
    }
//  디자인그룹
    function procProductUpdateDesignGroup($args){
        global $module_info;
        if(!$module_info->seq) return setReturn(-1,"잘못된 접근입니다");

        if(!isset($args->wrap)) return setReturn(-1,"등록할 디자인그룹을 추가 해 주세요.");

        $design_groups = array();
        foreach($args->wrap as $key => $val){
            if(!is_array($design_groups[$val])) $design_groups[$val] = array();
            $design_groups[$val][] = $args->group[$key];
        }

        $obj = new stdClass();
        $obj->design_group = serialize($design_groups);

        $query = updateQueryString("product",$obj);
        $query .= " where `product_srl` = " . $module_info->seq;
        sql_query($query);

        return setReturn(0,"디자인그룹이 재정렬 되었습니다.");
    }

//유통관리
    function procProductCircuratorInsert($args){
        global $module_info;
        if(!$module_info->seq) return setReturn(-1,"잘못된 접근입니다");

        if(!isset($args->circu_srls)) return setReturn(-1,"등록할 유통을 추가해주세요.");

        $obj = new stdClass();
        $obj->product_srl = $module_info->seq;
        foreach($args->circu_srls as $circu_subs){
            $circu_subs = explode("@",$circu_subs);
            $obj->circu_srl = $circu_subs[0];
            $obj->subs_srl = $circu_subs[1];

            $output = insertQuery("circu_product",$obj);
        }

        return setReturn(0,"성공적으로 등록되었습니다.(중복값 제외)");
    }

    function procProductCircuratorDelete($args){
        global $module_info;
        if(!$module_info->seq) return setReturn(-1,"잘못된 접근입니다");

        foreach($args->cart as $circu_srl){
            $query = "DELETE FROM `circu_product` WHERE `product_srl` = ".$module_info->seq." AND `circu_srl` = ".$circu_srl;
            sql_query($query);
        }

        return setReturn(0,"성공적으로 삭제 되었습니다.");
    }
// 판관비관리
    function procProductUpdateSaleFees($args){
        global $module_info;
        if(!$module_info->seq) return setReturn(-1,"잘못된 접근입니다");

        if(!isset($args->categories)) return setReturn(-1,"유통을 추가하여 판매법인목록을 먼저 구성하십시오.");

        if(!$args->subs_srl) return setReturn("적용할 법인이 선택되지 않았습니다.");
        //set fees fields.
        $settingsModel = &getModel('settings');
        $sales_fields = $settingsModel->getSalesfeesField();

        sql_begin();

        sql_query(sprintf("DELETE FROM `sale_fees` where `product_srl` = %d and `subs_srl` = %d",$module_info->seq,$args->subs_srl));

        $obj = new stdClass();
        $obj->product_srl = $module_info->seq;
        $obj->subs_srl = $args->subs_srl;
        foreach($args->categories as $category){
            $obj->category = $category;

            foreach($sales_fields as $field){
                $obj->{$field} = $args->{$category."_".$field};
            }

            $output = insertQuery("sale_fees",$obj);
            if(!$output->result){
                sql_rollback();
                return setReturn(-1,"판관비 변경에 실패했습니다.<br />값이 올바른지 확인하십시오.");
            }
        }
        sql_commit();

        return setReturn(0,"성공적으로 반영되었습니다.");
    }

// 생산비관리
    function procProductUpdateProcFees($args){
        global $module_info;
        if(!$module_info->seq) return setReturn(-1,"잘못된 접근입니다");

        if(!isset($args->categories)) return setReturn(-1,"매트릭스 레코드의 Prod_subs 필드를 통해 법인목록을 구성하십시오.");

        if(!$args->prod_subs) return setReturn("적용할 법인이 선택되지 않았습니다.");

        //set fees fields.
        $settingsModel = &getModel('settings');
        $sales_fields = $settingsModel->getProcfeesField();

        sql_begin();

        sql_query(sprintf("DELETE FROM `proc_fees` where `product_srl` = %d and `prod_subs` = '%s'",$module_info->seq,$args->prod_subs));

        $obj = new stdClass();
        $obj->product_srl = $module_info->seq;
        $obj->prod_subs = $args->prod_subs;
        $obj->prod_currency = $args->prod_currency;
        foreach($args->categories as $category){
            $obj->category = $category;

            foreach($sales_fields as $field){
                $obj->{$field} = $args->{$category."_".$field};
            }

            $output = insertQuery("proc_fees",$obj);
            if(!$output->result){
                sql_rollback();
                return setReturn(-1,"생산법인 데이터 변경에 실패했습니다.<br />값이 올바른지 확인하십시오.");
            }
        }
        sql_commit();

        return setReturn(0,"성공적으로 반영되었습니다.");
    }

    function procProductTypeUpdate($args){
        global $module_info;
        global $domain;

        if(!$module_info->seq) return setReturn(-1,"잘못된 접근입니다");

        $field_model = array();
        $field_spec = array();
        $field_th = array();
        foreach($args->field as $field){
            $field_th[$field] = $args->{$field . '_th'};

            $field_model[$field] = array();
            $field_model[$field]['model_display'] = $args->{$field . '_model_display'};
            $field_model[$field]['model_use'] = $args->{$field . '_model_use'} == "Y" ? "Y" : "N";
            $field_model[$field]['model_order'] = $args->{$field . '_model_order'};

            $field_spec[$field] = array();
            $field_spec[$field]['spec_display'] = $args->{$field . '_spec_display'};
            $field_spec[$field]['spec_use'] = $args->{$field . '_spec_use'} == "Y" ? "Y" : "N";
            $field_spec[$field]['spec_header'] = $args->{$field . '_spec_header'} == "Y" ? "Y" : "N";
            $field_spec[$field]['spec_order'] = $args->{$field . '_spec_order'};
        }

        $obj = new stdClass();
        $obj->field_th = serialize($field_th);
        $obj->field_model = serialize($field_model);
        $obj->field_spec = serialize($field_spec);
        $obj->product_title = $args->product_title;

        $query = updateQueryString("product",$obj);
        $query .= " where `product_srl` = " . $module_info->seq;

        sql_query($query);

        return setReturn(0, "수정되었습니다.", sprintf("%ssettings/dispProductTypeList/%d?tab=settingfield&dept_srl=%d" , $domain ,$module_info->seq,$args->dept_srl));

    }


//    제품유형 생성
    function procProductTypeInsert($args){
        global $module_info;
        global $domain;

        if ( isset($_FILES["csv_file"])) {

            //if there was an error uploading the file
            if ($_FILES["csv_file"]["error"] > 0) {
                return setReturn(-1, "CSV업로드 에러 : error " . $_FILES["csv_file"]["error"]);
            }
            else {
                if(strpos($_FILES["csv_file"]["name"],".csv") == false)
                    return setReturn(-1, "CSV파일만 사용할 수 있습니다");

                //CSV to Array by xiso
                $tmpName = $_FILES['csv_file']['tmp_name'];
                $csvString = file($tmpName);

//                $data = str_getcsv($csvString[0], "\r"); //parse the rows

                $csvArray = array();
                foreach($csvString as $row) $csvArray[] = str_getcsv($row, ","); //parse the items in rows
//                unset($data, $row);

                //Array to Data
                $fields = array();
                $matrix = array();

                $check_common_fields = array();
                foreach($csvArray as $key => $val){
                    //1,2번줄은 분류와 필드제목.
                    if($key == 0) continue;
                    if($key == 1){
                        foreach($val as $k => $field){

                            $pure_field = strtolower(preg_replace("/\s+/", "", $field));
                            $pure_field = preg_replace("/[^a-zA-Z/s", "", $pure_field);

                            if(strpos($csvArray[0][$k],"common") !== false){
                                $check_common_fields[] = $pure_field;
                            }

                            $fields[$k]["field"] = $pure_field;
                            $fields[$k]["th_title"] = $csvArray[0][$k];
//                            field_model
                            $fields[$k]["model_display"] = $field;
                            $fields[$k]["model_use"] = "N";
                            $fields[$k]["model_order"] = $k * 10;
//                            field_spec
                            $fields[$k]["spec_display"] = $field;
                            $fields[$k]["spec_use"] = "N";
                            $fields[$k]["spec_order"] = $k * 10;
                        }
                    }else{
                        $data = array();
                        foreach($val as $k => $v){
                            $pure_field = strtolower(preg_replace("/\s+/", "", $csvArray[1][$k]));
                            $pure_field = preg_replace("/[^a-zA-Z/s", "", $pure_field);
                            $data[$pure_field] = $v;
                        }
                        $matrix[] = $data;
                    }
                }
                //check fields
                $common_fields = array("no","model","mat_cost","st","sutuff_qty","prod_subs","design_group","design_ex_img","design_in_img","use_prm");
                foreach($common_fields as $field){
                    if(!in_array($field,$check_common_fields)){
                        return setReturn(-1,sprintf("필수 필드인 [%s]필드가 누락되었습니다.",$field));
                    }
                }

                //check table exists
                $table_exists = sql_query('select 1 from `product_{$args->table_id}`');
                if($table_exists !== FALSE) return setReturn(-1,"이미 존재하는 테이블ID 입니다.");

                if(!$args->product_title) return setReturn("product_type명이 입력되지 않았습니다.");

                if(!$args->dept_srl) return setReturn("사업부가 선택되지 않았습니다.");


                //트랜잭션 시작.
                $obj = new stdClass();
                sql_begin();

                //input product record.
                $obj->product_srl = getNextSequence();
                $obj->product_title = $args->product_title;
                $obj->dept_srl = $args->dept_srl;
                $obj->table_id = $args->table_id;

                $field_model = array();
                $field_spec = array();
                $field_th = array();
                foreach($fields as $field){
                    $field_th[$field["field"]] = $field["th_title"];

                    $field_model[$field['field']] = array();
                    $field_model[$field['field']]['model_display'] = $field['model_display'];
                    $field_model[$field['field']]['model_use'] = $field['model_use'];
                    $field_model[$field['field']]['model_order'] = $field['model_order'];

                    $field_spec[$field['field']] = array();
                    $field_spec[$field['field']]['spec_display'] = $field['spec_display'];
                    $field_spec[$field['field']]['spec_use'] = $field['spec_use'];
                    $field_spec[$field['field']]['spec_order'] = $field['spec_order'];
                }
                $obj->field_th = serialize($field_th);
                $obj->field_model = serialize($field_model);
                $obj->field_spec = serialize($field_spec);
                $obj->regdate = date("YmdHis");
                $obj->list_order = $obj->product_srl * -1;
                $insert_result = insertQuery("product",$obj);
                if(!$insert_result->result){
                    sql_rollback();
                    return setReturn(-1,"필드리스트 생성에 실패했습니다.<br />" . $insert_result->message);
                }


                //create product table.
                $table_sql = "CREATE TABLE IF NOT EXISTS `product_{$args->table_id}` (";
                $table_sql .= " `no` bigint(11) NOT NULL AUTO_INCREMENT,";
                $table_sql .= " `model` varchar(100) NOT NULL,";
                $table_sql .= " `mat_cost` double NOT NULL,";
                $table_sql .= " `st` varchar(20) NOT NULL,";
                $table_sql .= " `sutuff_qty` double NOT NULL,";
                $table_sql .= " `prod_subs` varchar(100) NOT NULL,";
                $table_sql .= " `design_group` varchar(100) NOT NULL,";
                $table_sql .= " `design_ex_img` varchar(255) NOT NULL,";
                $table_sql .= " `design_in_img` varchar(255) NOT NULL,";
                $table_sql .= " `use_prm` char(1) NOT NULL DEFAULT 'Y',";

                //create custom field
                foreach($fields as $field){
                    //continue common_field
                    if(in_array($field['field'],$common_fields)) continue;
                    $table_sql .= " `{$field['field']}` varchar(100) NOT NULL,";
                }

                $table_sql .= " PRIMARY KEY (`no`),";
                $table_sql .= " KEY `model` (`model`,`prod_subs`,`design_group`,`use_prm`)";
                $table_sql .= " ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

                $output = sql_query($table_sql);
                if(!$output){
                    sql_rollback();

                    global $g5;
                    $link = $g5['connect_db'];

                    return setReturn(-1,"매트릭스 생성에 실패했습니다.<br />" . mysqli_errno($link) . " : " .  mysqli_error($link));
                }


                //생성된 테이블에 값 집어넣기.
                foreach($matrix as $data){
                    $result = insertQuery("product_".$args->table_id , $data);
                    sql_rollback();
                    if(!$result->result)
                        return setReturn(-1, "매트릭스 데이터가 잘못되었습니다. [no : " . $data['no'] . "]");
                }

                //종료.
                sql_commit();

                return setReturn(0, "Product Type 생성 완료.", sprintf("%ssettings/dispProductTypeList/%d?dept_srl=%d&tab=settingfield" ,$domain ,$obj->product_srl,$obj->dept_srl));
            }
        } else {
            return setReturn(-1, "업로드할 파일이 선택되지 않았습니다.");
        }

        exit();
    }
}