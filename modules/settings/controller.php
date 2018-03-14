<?php

class settingsController{
    function init(){
    }

    function procMemberInsert($args){
        //check fields
        if(!$args->user_id) return setReturn(-1, "유저 아이디가 누락되었습니다.");

        //member first
        $obj = new stdClass();
        $obj->user_id = $args->user_id;
        $obj->emp_no = $args->emp_no;
        $obj->user_name = $args->user_name;
        $obj->dept = $args->dept;
        $obj->rank = $args->rank;
        $obj->is_denied = $args->is_denied;
        $obj->is_admin = $args->is_admin;
        $obj->permissions = serialize($args->permissions);
        $obj->circu_srls = serialize($args->circu_srls);

        if($args->member_srl){
            if($args->password){
                $obj->password = sha1($args->password);
            }
            $query = updateQueryString("member",$obj);
            $query .= " where `member_srl` = ". $args->member_srl;

            $result = sql_query($query);
            if(!$result){
                return setReturn(0,"업데이트에 실패하였습니다.");
            }else {
                return setReturn(0, "수정성공");
            }
        }else{
            if(!$args->password) return setReturn(-1, "패스워드는 반드시 입력해야합니다.");
            //중복체크
            $oMemberModel = getModel('member');
            $member_info = $oMemberModel->getMemberInfoByUserId($args->user_id)->data;
            if($member_info->member_srl) return setReturn(-1, "이미 존재하는 아이디입니다.");

            $obj->password = sha1($args->password);
            $obj->regdate = date("YmdHis");
            $obj->member_srl = getNextSequence();
            $output = insertQuery("member",$obj);
            if($output->result){
                return setReturn(0, "등록성공");
            }else{
                return setReturn(-1, "등록 실패");
            }
        }
    }

    function procMemberDelete(){
        global $module_info;
        if(!$module_info->seq) return setReturn(-1,"잘못된 접근입니다");

        $oMemberModel = getModel('member');
        $member_info = $oMemberModel->getMemberInfoByMemberSrl($module_info->seq)->data;
        if($member_info->is_admin == "Y"){
            return setReturn(-1,"최고관리자는 삭제할 수 없습니다.");
        }

        $result = sql_query("delete from `member` where `member_srl` = {$module_info->seq}");
        if(!$result){
            return setReturn(-1,"삭제에 실패하였습니다.");
        }else {
            return setReturn(0, "삭제되었습니다.");
        }

    }

    function procInsertPermission($args){
        $is_update = "N";
        $obj = new stdClass();
        if($args->type_srl){
            $obj->type_srl = $args->type_srl;
            $is_update = "Y";
        }else{
            $obj->type_srl = getNextSequence();
        }
        $obj->product_srl = $args->product_srl;
        $obj->type_title = $args->type_title;
        $obj->permission = serialize($args->permission);

        if($is_update == "Y"){
            $query = updateQueryString("member_type",$obj);
            $query .= " where `type_srl` = ". $obj->type_srl;

            $result = sql_query($query);
            if(!$result){
                return setReturn(0,"업데이트에 실패하였습니다.");
            }else{
                return setReturn(0, "수정성공");
            }
        }else{
            $output = insertQuery("member_type",$obj);
            if($output->result){
                return setReturn(0, "등록성공");
            }else{
                return setReturn(-1, "등록 실패");
            }
        }
    }

//    법인수정
    function procUpdateSubsdiary($args){
        //start transition
        sql_begin();
        foreach($args->subs_title as $subs_srl => $subs_title){
            $obj = new stdClass();
            $obj->subs_title = $args->subs_title[$subs_srl];
            $obj->region = $args->region[$subs_srl];
            $obj->currency = $args->currency[$subs_srl];

            $query = updateQueryString("subs",$obj);
            $query .= " where `subs_srl` = ". $subs_srl;

            $result = sql_query($query);
            if(!$result){
                sql_rollback();
                return setReturn(0,"업데이트에 실패하였습니다.");
            }
        }
        sql_commit();
        return setReturn(0,"성공적으로 처리하였습니다.");
    }

//    법인생성
    function procInsertSubsdiary($args){
        $obj = new stdClass();
        $obj->subs_srl = getNextSequence();
        $obj->subs_title = $args->subs_title;
        $obj->region = $args->region;
        $obj->currency = $args->currency;
        $obj->regdate = date("YmdHis");
        $obj->list_order = $obj->circu_srl * -1;

        $output = insertQuery("subs",$obj);
        if($output->result){
            return setReturn(0, "등록성공");
        }else{
            return setReturn(-1, "등록 실패");
        }
    }
//    유통수정
    function procUpdateCircurator($args){
        //start transition
        sql_begin();
        foreach($args->circu_title as $circu_srl => $circu_title){
            $obj = new stdClass();
            $obj->circu_title = $args->circu_title[$circu_srl];
            $obj->circu_title_abb = $args->circu_title_abb[$circu_srl];

            $query = updateQueryString("circu",$obj);
            $query .= " where `circu_srl` = ". $circu_srl;

            $result = sql_query($query);
            if(!$result){
                sql_rollback();
                return setReturn(0,"업데이트에 실패하였습니다.");
            }
        }
        sql_commit();
        return setReturn(0,"성공적으로 처리하였습니다.");
    }

//    유통생성
    function procInsertCircurator($args){
        if(!isset($args->subs_srl)) return setReturn(-1,"소속 법인을 선택하십시요.");
        $obj = new stdClass();
        $obj->circu_srl = getNextSequence();
        $obj->subs_srl = $args->subs_srl;
        $obj->circu_title = $args->circu_title;
        $obj->circu_title_abb = $args->circu_title_abb;
        $obj->regdate = date("YmdHis");
        $obj->list_order = $obj->circu_srl * -1;

        $output = insertQuery("circu",$obj);
        if($output->result){
            return setReturn(0, "등록성공");
        }else{
            return setReturn(-1, "등록 실패");
        }
    }

//통화 생성
    function procInsertCurrency($args){
        if(!isset($args->code)) return setReturn(-1,"통화 코드가 누락되었습니다.");
        $args->code = strtoupper($args->code);

        $sql = "select * from `currency` where `code` = '" . $args->code . "'";
        $currency = sql_fetch($sql);
        if($currency['title']){
            return setReturn(-1, sprintf("등록하신 코드 %s는 이미 \"%s\" 으로 등록되어 있습니다.",$currency['code'],$currency['title']));
        }

        if(!isset($args->title)) return setReturn(-1,"통화명이 누락되었습니다.");
        if(!isset($args->char)) return setReturn(-1,"통화 설명이 누락되었습니다.");
        if(!isset($args->rate)) $args->rate = "1.0";

        $obj = new stdClass();
        $obj->code = $args->code;
        $obj->title = $args->title;
        $obj->char = $args->char;
        $obj->to_usd = $args->rate;

        $output = insertQuery("currency",$obj);
        if($output->result){
            return setReturn(0, "등록성공");
        }else{
            return setReturn(-1, "등록 실패");
        }
    }

//통화 업데이트
    function procUpdateCurrency($args){
        //start transition
        sql_begin();
        foreach($args->to_usd as $code => $to_usd){
            $obj = new stdClass();
            $obj->to_usd = $to_usd;
            $query = updateQueryString("currency",$obj);
            $query .= " where `code` = '" . $code ."'";

            $result = sql_query($query);
            if(!$result){
                sql_rollback();
                return setReturn(0,"환율 업데이트에 실패하였습니다.");
            }
        }
        sql_commit();
        return setReturn(0,"성공적으로 처리하였습니다.");
    }

//    스탭업로직 변경/삭제
    function procProductUpdateStepup($args){
        global $module_info;
        if(!$module_info->seq) return setReturn(-1,"잘못된 접근입니다");

        $obj = new stdClass();
        $stepup_logic = unserialize($args->ori_stepup);
        if(!is_array($stepup_logic)) $stepup_logic = array();

        if($args->is_delete == 'Y'){
            unset($stepup_logic[$args->logic_key]);
            unlink($args->img_url);
        }else{
            //db에등록할 데이터를 대체
            $logic = new stdClass();

            if(is_uploaded_file($_FILES['stepup_upload']['tmp_name'])){

                $file_info = $_FILES['stepup_upload'];
                // A workaround for Firefox upload bug
                if(preg_match('/^=\?UTF-8\?B\?(.+)\?=$/i', $file_info['name'], $match))
                {
                    $file_info['name'] = base64_decode(strtr($match[1], ':', '/'));
                }

                // https://github.com/xpressengine/xe-core/issues/1713
                $file_info['name'] = preg_replace('/\.(php|phtm|phar|html?|cgi|pl|exe|jsp|asp|inc)/i', '$0-x',$file_info['name']);
                // $file_info['name'] = removeHackTag($file_info['name']);
                $file_info['name'] = str_replace(array('<','>'),array('%3C','%3E'),$file_info['name']);

                // Get random number generator
                $random = new Password();

                // Set upload path by checking if the attachement is an image or other kinds of file
                if(preg_match("/\.(jpe?g|gif|png)$/i", $file_info['name']))
                {
                    $path = "./files/stepup_logic/" . $module_info->seq . "/";
                    FileHandler::makeDir($path);

                    $_file_ext = substr(strrchr($file_info['name'],'.'),1);
                    $_file_name = $random->createSecureSalt(32, 'hex');

                    $filename = $_file_name. '.' .$_file_ext;
                }
                else
                {
                    return setReturn(-1, "이미지 형식이 아닙니다.");
                }

                //업로드
                if(!@move_uploaded_file($file_info['tmp_name'], $path . $filename))
                    return setReturn(-1,'로직 이미지 업로드에 실패했습니다.');

                //주소변경
                unlink($args->img_url);
                $logic->img_url = $path . $filename;
            }else{
                $logic->img_url = $args->img_url;
            }

            $logic->title = $args->stepup_title;
            $logic->description = $args->stepup_description;

            $stepup_logic[$args->logic_key] = $logic;
        }
        $obj->stepup_logic = serialize($stepup_logic);

        $query = updateQueryString("product",$obj);
        $query .= " where `product_srl` = " . $module_info->seq;
        sql_query($query);

        return setReturn(0,"성공적으로 처리하였습니다.");
    }

//    스탭업로직 저장
    function procProductUploadStepup($args){
        global $module_info;
        if(!$module_info->seq) return setReturn(-1,"잘못된 접근입니다");

        $obj = new stdClass();
        $stepup_logic = unserialize($args->ori_stepup);
        if(!is_array($stepup_logic)) $stepup_logic = array();

        if(!$args->stepup_title) return setReturn(-1,"스탭업 로직의 제목을 입력하세요.");
        if(!$args->stepup_description) return setReturn(-1,"스탭업 로직의 설명글을 입력하세요.");
        if(!is_uploaded_file($_FILES['stepup_upload']['tmp_name'])) return setReturn(-1,"로직이미지가 업로드되지 않았습니다.");

        $file_info = $_FILES['stepup_upload'];
        // A workaround for Firefox upload bug
        if(preg_match('/^=\?UTF-8\?B\?(.+)\?=$/i', $file_info['name'], $match))
        {
            $file_info['name'] = base64_decode(strtr($match[1], ':', '/'));
        }

        // https://github.com/xpressengine/xe-core/issues/1713
        $file_info['name'] = preg_replace('/\.(php|phtm|phar|html?|cgi|pl|exe|jsp|asp|inc)/i', '$0-x',$file_info['name']);
        // $file_info['name'] = removeHackTag($file_info['name']);
        $file_info['name'] = str_replace(array('<','>'),array('%3C','%3E'),$file_info['name']);

        // Get random number generator
        $random = new Password();

        // Set upload path by checking if the attachement is an image or other kinds of file
        if(preg_match("/\.(jpe?g|gif|png)$/i", $file_info['name']))
        {
            $path = "./files/stepup_logic/" . $module_info->seq . "/";
            FileHandler::makeDir($path);

            $_file_ext = substr(strrchr($file_info['name'],'.'),1);
            $_file_name = $random->createSecureSalt(32, 'hex');

            $filename = $_file_name. '.' .$_file_ext;
        }
        else
        {
            return setReturn(-1, "이미지 형식이 아닙니다.");
        }

        //업로드
        if(!@move_uploaded_file($file_info['tmp_name'], $path . $filename))
            return setReturn(-1,'로직 이미지 업로드에 실패했습니다.');

        //db에등록할 데이터를 대체 (기존아이콘이 있거나말거나 새로업로드된게 있으면 대체)
        $logic = new stdClass();
        $logic->img_url = $path . $filename;
        $logic->title = $args->stepup_title;
        $logic->description = $args->stepup_description;

        $stepup_logic[time()] = $logic;
        $obj->stepup_logic = serialize($stepup_logic);

        $query = updateQueryString("product",$obj);
        $query .= " where `product_srl` = " . $module_info->seq;
        sql_query($query);

        return setReturn(0,"스탭업 로직이 추가 되었습니다.");
    }
//    디자인파일 저장
    function procProductDesignUpload($args){
        global $module_info;
        if(!$module_info->seq) return setReturn(-1,"잘못된 접근입니다");


        //삭제할 이미지를 삭제 (새로업로드 되었을 수 있으므로 먼저삭제해야함)
        if(is_array($args->deleted)){
            foreach($args->deleted as $val){
                if(file_exists($val))
                    unlink($val);
            }
        }

        //제품이미지를 일단 먼저업로드 (기존아이콘 삭제하면서 바로 대체)
        foreach($_FILES as $key => $file){
            $key_arr = explode("|@|",$key);
            $inex = $key_arr[0];
            $code = $key_arr[1];

            if(is_uploaded_file($file['tmp_name'])){
                $file_info = $file;

                // A workaround for Firefox upload bug
                if(preg_match('/^=\?UTF-8\?B\?(.+)\?=$/i', $file_info['name'], $match))
                {
                    $file_info['name'] = base64_decode(strtr($match[1], ':', '/'));
                }

                // https://github.com/xpressengine/xe-core/issues/1713
                $file_info['name'] = preg_replace('/\.(php|phtm|phar|html?|cgi|pl|exe|jsp|asp|inc)/i', '$0-x',$file_info['name']);
                // $file_info['name'] = removeHackTag($file_info['name']);
                $file_info['name'] = str_replace(array('<','>'),array('%3C','%3E'),$file_info['name']);

                // Get random number generator
                $random = new Password();

                // Set upload path by checking if the attachement is an image or other kinds of file
                if(preg_match("/\.(jpe?g)$/i", $file_info['name']))
                {
                    $path = "./files/images/" . $module_info->seq . "/" . $inex . "/";
                    FileHandler::makeDir($path);

                    $_file_ext = substr(strrchr($file_info['name'],'.'),1);
                    $_file_name = $code;

                    $filename = $_file_name. '.' .$_file_ext;
                    $filename = strtolower($filename);
                }
                else
                {
                    return setReturn(-1, "JPG 또는 JPEG 이미지만 업로드할 수 있습니다.");
                }

                //기존이미지 삭제
                if(file_exists($path . $filename))
                    unlink($path . $filename);

                //업로드
                if(!@move_uploaded_file($file_info['tmp_name'], $path . $filename))
                    return setReturn(-1,'아이콘 업로드에 실패했습니다.');
            }
        }
        return setReturn(0, "저장되었습니다.");
    }
//옵션저장
    function procProductOptionUpdate($args){
        global $module_info;
        if(!$module_info->seq) return setReturn(-1,"잘못된 접근입니다");

        $oProduct = sql_fetch("select * from `product` where `product_srl` = " . $module_info->seq);
        $table_id = $oProduct['table_id'];

        //아이콘을 일단 먼저업로드 (기존아이콘 삭제하면서 바로 대체)
        $icons = array();
        foreach($_FILES as $key => $file){
            $key_arr = explode("|@|",$key);
            $key_id = $key_arr[0] . "_" . $key_arr[1];
            if(is_uploaded_file($file['tmp_name'])){
                $file_info = $file;

                // A workaround for Firefox upload bug
                if(preg_match('/^=\?UTF-8\?B\?(.+)\?=$/i', $file_info['name'], $match))
                {
                    $file_info['name'] = base64_decode(strtr($match[1], ':', '/'));
                }

                // https://github.com/xpressengine/xe-core/issues/1713
                $file_info['name'] = preg_replace('/\.(php|phtm|phar|html?|cgi|pl|exe|jsp|asp|inc)/i', '$0-x',$file_info['name']);
                // $file_info['name'] = removeHackTag($file_info['name']);
                $file_info['name'] = str_replace(array('<','>'),array('%3C','%3E'),$file_info['name']);

                // Get random number generator
                $random = new Password();

                // Set upload path by checking if the attachement is an image or other kinds of file
                if(preg_match("/\.(jpe?g|gif|png)$/i", $file_info['name']))
                {
                    $path = "./files/icons/" . $module_info->seq . "/";
                    FileHandler::makeDir($path);

                    $_file_ext = substr(strrchr($file_info['name'],'.'),1);
                    $_file_name = $key_id;

                    $filename = $_file_name. '.' .$_file_ext;
                }
                else
                {
                    return setReturn(-1, "이미지 형식이 아닙니다.");
                }

                //기존아이콘 삭제
                if(file_exists($path . $filename))
                    unlink($path . $filename);

                //업로드
                if(!@move_uploaded_file($file_info['tmp_name'], $path . $filename))
                    return setReturn(-1,'아이콘 업로드에 실패했습니다.');

                //db에등록할 데이터를 대체 (기존아이콘이 있거나말거나 새로업로드된게 있으면 대체)
                $args->{$key} = $path . $filename;
            }
        }

        $insert_obj = array();
        foreach($args as $key => $val){
            $key_arr = explode("|@|",$key);
            $key_id = $key_arr[0] . "_" . $key_arr[1];

            //db에등록할 데이터 정리
            if(!$insert_obj[$key_id]){
                $obj = new stdClass();
                $obj->table_id = $table_id;
                $obj->field = $key_arr[0];
                $obj->record = $key_arr[1];
            }else{
                $obj = $insert_obj[$key_id];
            }
            $obj->{$key_arr[2]} = $val;

            $insert_obj[$key_id] = $obj;
        }

        //기존 데이터를 전부 제거하고
        sql_query("delete from `options` where `table_id` = '{$table_id}'");
        //insert
        foreach($insert_obj as $obj){
            $output = insertQuery("options",$obj);
        }

        if($output->result){
            return setReturn(0, "등록성공");
        }else{
            return setReturn(-1, "옵션 등록에 실패하였습니다.");
        }
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

        $obj = new stdClass();
        if(isset($args->wrap)){
            $design_groups = array();
            foreach($args->wrap as $key => $val){
                if(!is_array($design_groups[$val])) $design_groups[$val] = array();
                $design_groups[$val][] = $args->group[$key];
            }
        }else{
            $obj->design_group = false;
        }

        $obj->design_group = serialize($design_groups);

        $query = updateQueryString("product",$obj);
        $query .= " where `product_srl` = " . $module_info->seq;
        sql_query($query);

        return setReturn(0,"디자인그룹이 재정렬 되었습니다.");
    }
//  마컴물
    function procProductUpdateMacom($args){
        global $module_info;
        if(!$module_info->seq) return setReturn(-1,"잘못된 접근입니다");

        $obj = new stdClass();
        if(isset($args->header)){
            $macom_list = array();
            foreach($args->header as $no => $header){
                if(!is_array($macom_list[$header])) $macom_list[$header] = array();

                array_push($macom_list[$header],array(
                                    "header" => $header,
                                    "title" => $args->title[$no],
                                    "path" => $args->path[$no])
                );
            }
        }else{
            $obj->macom_list = false;
        }
        $obj->macom_list = serialize($macom_list);

        $query = updateQueryString("product",$obj);
        $query .= " where `product_srl` = " . $module_info->seq;
        sql_query($query);

        return setReturn(0,"마컴물이 재 정렬 되었습니다.");
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
        global $g5;
        $link = $g5['connect_db'];
        if(!$module_info->seq) return setReturn(-1,"잘못된 접근입니다");

        if(!isset($args->categories)) return setReturn(-1,"매트릭스 레코드의 Prod_subs 필드를 통해 법인목록을 구성하십시오.");

        if(!$args->prod_subs) return setReturn("적용할 법인이 선택되지 않았습니다.");

        //set fees fields.
        $settingsModel = &getModel('settings');
        $sales_fields = $settingsModel->getProcfeesField();

        sql_begin($link);

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
        sql_commit($link);

        return setReturn(0,"성공적으로 반영되었습니다.");
    }

//유형 삭제
    function procProductTypeDelete($args){
        global $module_info;
        global $domain;

        if(!$module_info->seq) return setReturn(-1,"잘못된 접근입니다");

        $product_srl = $module_info->seq;

        $oProduct = sql_fetch("select * from `product` where `product_srl` = " . $product_srl);

        //제품테이블 삭제 (성공유무 무시)
        sql_query("DROP TABLE product_" . $oProduct['table_id']);
        sql_query("delete from `options` where `table_id` = '" . $oProduct['table_id'] . "'");

        //product 삭제
        sql_query("delete from `product` where `product_srl` = " . $product_srl);
        sql_query("delete from `proc_fees` where `product_srl` = " . $product_srl);
        sql_query("delete from `sale_fees` where `product_srl` = " . $product_srl);
        sql_query("delete from `circu_product` where `product_srl` = " . $product_srl);
        sql_query("delete from `prm` where `product_srl` = " . $product_srl);

        return setReturn(0, "삭제되었습니다.", sprintf("%ssettings/dispProductTypeList" , $domain));
    }
//제품유형 수정
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

            $field_info[$field] = array();
            $field_info[$field]['info_use'] = $args->{$field . '_info_use'} == "Y" ? "Y" : "N";
            $field_info[$field]['info_order'] = $args->{$field . '_info_order'};
        }

        $obj = new stdClass();
        $obj->field_th = serialize($field_th);
        $obj->field_model = serialize($field_model);
        $obj->field_spec = serialize($field_spec);
        $obj->field_info = serialize($field_info);
        $obj->product_title = $args->product_title;

        $query = updateQueryString("product",$obj);
        $query .= " where `product_srl` = " . $module_info->seq;

        sql_query($query);

        return setReturn(0, "수정되었습니다.", sprintf("%ssettings/dispProductTypeList/%d?tab=settingfield&dept_srl=%d" , $domain ,$module_info->seq,$args->dept_srl));

    }

    function replaceField($str , $is_num = false){
        if($is_num == false){
            $pure_field = preg_replace('#[^a-zA-Z_]#', '', $str);
        }else{
            $pure_field = preg_replace("/[^a-zA-Z0-9]/", '', $str);
        }

        return strtolower($pure_field);
    }

    /*
     * 데이터센터
     * 2018.03.14
    */
    function __commonCSVCheck($args){
        if ( isset($_FILES["csv_file"])) {

            //if there was an error uploading the file
            if ($_FILES["csv_file"]["error"] > 0) {
                return setReturn(-1, "CSV업로드 에러 : error " . $_FILES["csv_file"]["error"]);
            }else{
                //CSV to Array by xiso
                $tmpName = $_FILES['csv_file']['tmp_name'];
                $csvString = file($tmpName);

                $csvArray = array();
                foreach($csvString as $row) $csvArray[] = str_getcsv($row, ","); //parse the items in rows

                $output = new Object();
                $output->fields = $csvArray[0];

                //field replace
                foreach($output->fields as $key => $field) $output->fields[$key] = $this->replaceField($field);

                unset($csvArray[0]);
                foreach($csvArray as $data){
                    $data_obj = new stdClass();
                    foreach($data as $key => $val){
                        $data_obj->{$output->fields[$key]} = $val;
                    }
                    $output->data[] = $data_obj;
                }
                return $output;
            }
        }else{
            return setReturn(-1, "업로드할 파일이 선택되지 않았습니다.");
        }
    }
    function procDataInsertSubsidiary($args){
        global $module_info;
        global $domain;
        //check CSV
        $output = $this->__commonCSVCheck($args);
        if($output->error) return $output;
//        [0] => stdClass Object
//            (
//            [region] => LGEKR
//            [subs_title] => Asia
//            [currency] => KRW
//            )
        $exists = array();
        foreach($output->data as $subs){
            $data_exists = sql_query('select * from `subs` where `subs_title` = "' . $subs->subs_title .'"');
            $row = mysqli_num_rows($data_exists);
            if($row > 0){
                $exists[] = $subs->subs_title;
            }else{
                $subs->subs_srl = getNextSequence();
                $subs->regdate = date("YmdHis");
                $subs->list_order = $subs->subs_srl * -1;
                if(!$subs->currency) $subs->currency = "USD";
                $insert_result = insertQuery("subs",$subs);
                if(!$insert_result->result){
                    return setReturn(-1,"필드리스트 생성에 실패했습니다.<br />" . $insert_result->message);
                }
            }
        }
        $message = "판매법인 추가가 완료되었습니다.";
        if(count($exists) > 0){
            $message .= "<br />" . count($exists) . "개의 중복데이터는 건너뛰었습니다.";
            $message .= join(",",$exists);
        }
        return setReturn(0, $message, sprintf("%ssettings/dispDatacenter" ,$domain));

        exit();
    }

//    제품유형 생성
    function procProductTypeInsert($args){
        global $module_info;
        global $domain;
        global $g5;
        $link = $g5['connect_db'];

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

                            $pure_field = $this->replaceField($field);

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

                            $fields[$k]["info_use"] = "N";
                            $fields[$k]["use_order"] = $k * 10;
                        }
                    }else{
                        $data = array();
                        foreach($val as $k => $v){
                            $data[$this->replaceField($csvArray[1][$k])] = $v;
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
                sql_begin($link);

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

                    $field_info[$field['field']] = array();
                    $field_info[$field['field']]['info_use'] = $field['info_use'];
                    $field_info[$field['field']]['info_order'] = $field['info_order'];
                }
                $obj->field_th = serialize($field_th);
                $obj->field_model = serialize($field_model);
                $obj->field_spec = serialize($field_spec);
                $obj->field_info = serialize($field_info);
                $obj->regdate = date("YmdHis");
                $obj->list_order = $obj->product_srl * -1;
                $insert_result = insertQuery("product",$obj);
                if(!$insert_result->result){
                    sql_rollback($link);
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

                $output = sql_query($table_sql, $link);
                if(!$output){
                    writeLog($table_sql,"create_table");
                    sql_rollback($link);
                    return setReturn(-1,"매트릭스 생성에 실패했습니다.<br />" . mysqli_errno($link) . " : " .  mysqli_error($link));
                }


                //생성된 테이블에 값 집어넣기.
                foreach($matrix as $data){
                    $result = insertQuery("product_".$args->table_id , $data);
                    sql_rollback($link);
                    if(!$result->result)
                        return setReturn(-1, "매트릭스 데이터가 잘못되었습니다. [no : " . $data['no'] . "]");
                }

                //종료.
                sql_commit($link);

                return setReturn(0, "Product Type 생성 완료.", sprintf("%ssettings/dispProductTypeList/%d?dept_srl=%d&tab=settingfield" ,$domain ,$obj->product_srl,$obj->dept_srl));
            }
        } else {
            return setReturn(-1, "업로드할 파일이 선택되지 않았습니다.");
        }

        exit();
    }
}