<?php

class prmModel{

    function init(){

    }

    function getModelHTML($model,$field_info){
        $use_fields = array();
        foreach($field_info as $field => $info){
            if($info['info_use'] == "Y"){
                $use_fields[$info['info_order']] = $field;
            }
        }
        sort($use_fields);

        $html = "<h4>{$model->model} ({$model->prod_subs})</h4>";
        foreach($use_fields as $field){
            $html .= "<p>" . $model->{$field} . "</p>";
        }

        return $html;
    }

    //select models search
    function getModels($args){
        if(!$args->table_id) return new Object(-1, "검색 대상이 지정되지 않았습니다.");
        $table_id = $args->table_id;

        if(count($args->conditions) > 0){
            $where = array();
            foreach($args->conditions as $key => $val){
                $val = addslashes($val);
                $where[] = "`$key` = '$val'";
            }
            $where = join(" and ",$where);
        }else{
            $where = "1";
        }

        $query = sprintf("select * from product_%s where %s",$table_id,$where);
        $list = sql_query_array($query);
        $output = new Object();
        $output->model_list = $list->data;

        $field_info = sql_fetch("SELECT `field_info` FROM `product` where `table_id` = '{$table_id}'");
        $field_info = unserialize($field_info['field_info']);

        foreach($output->model_list as $key => $model){
            $model->infohtml = $this->getModelHTML($model,$field_info);
            $output->model_list[$key] = $model;
        }

        return $output;
    }

    //검색조건을 읽어옴
    function getEnableFields($args){
        if(!$args->table_id) return new Object(-1, "검색 대상이 지정되지 않았습니다.");
        $table_id = $args->table_id;

        global $module_info;
        $where = array();

        //design_id가 있는지 봐야함.
        //design_id만 확인하면 되므로 lite한 데이터를 일단 가져옴.
        $litePRM = sql_fetch("select * from `prm` where `prm_srl` = " . $module_info->seq);
        if($litePRM['design_id']){
            $where[] = "`design_group` like '%" . $litePRM['design_id'] . "%'";
        }

        if(count($args->conditions) > 0){
            foreach($args->conditions as $key => $val){
                $val = addslashes($val);
                $where[] = "`$key` = '$val'";
            }
        }

        if(!count($where)){
            $where = "1";
        }else{
            $where = join(" and ",$where);
        }

        $search_fields = explode(",",$args->search_fields);
        $enable_fields = array();
        foreach($search_fields as $field){
            $query = sprintf("select %s from product_%s where %s group by %s",$field,$table_id,$where,$field);
            $list = sql_query_array($query);

            $enable_fields[$field] = array();
            foreach($list->data as $val){
                $enable_fields[$field][] = $val->{$field};
            }
        }

        $output = new Object();
        $output->enable_fields = $enable_fields;
        $output->where = $where;
        return $output;
    }

    //사업부가 가지고있는 제품유형을 먼저 선택
    function getProductListByDept($args){
        if(!$args->dept_srl) return new Object(-1, '사업부가 선택되지 않았습니다.');

        $query = "select * from `product` where `dept_srl` = " . $args->dept_srl;
        return sql_query_array($query);
    }

    //제품유형으로 가지고있는 법인과 유통정보를 뽑아옴
    function getSubsCorpByProduct($args){
        if(!$args->product_srl) return new Object(-1, '제품유형이 선택되지 않았습니다.');


        $query = "SELECT `circu`.`circu_srl`, `circu`.`circu_title`, `circu`.`circu_title_abb`, `subs`.`subs_srl`, `subs`.`subs_title`, `subs`.`region`, `subs`.`currency`";
        $query .= " FROM `circu_product` left join `subs` on `subs`.`subs_srl` = `circu_product`.`subs_srl`";
        $query .= " left join `circu` on `circu`.`circu_srl` = `circu_product`.`circu_srl`";
        $query .= " order by `subs`.`subs_title` ASC, `circu`.`circu_title` ASC";
        return sql_query_array($query);
    }

    //result용 객체 생성
    //PRM SRL만 있다면, 미리 아래쪽의 getPRM으로 PRM객체를 우선생성하고 돌려준다.
    function getResult($oPRM){
        //퍼블릭된 같은 법인의 다른유통 PRM을 가져옴.
        //뒤에는 현재의 PRM을 똑같이 취급하기위해
        $query = "SELECT * 
              FROM (
                SELECT  `table_id`, `circu_srl` ,  `design_id` ,  `profit` ,  `models` 
                FROM  `prm` 
                WHERE  `product_srl` = %d
                    AND (
                        (
                        `is_public` =  \"Y\"
                        AND  `subs_srl` = %d
                        AND  `circu_srl` <> %d
                        )
                    OR  `prm_srl` = %d
                    )
                GROUP BY  `circu_srl` 
                ORDER BY  `pubdate` DESC
              ) AS  `circus` 
              LEFT JOIN  `circu` ON (  `circu`.`circu_srl` =  `circus`.`circu_srl` ) 
              ORDER BY  `circu_title` ASC";
        $query_string = sprintf($query, $oPRM->product_srl, $oPRM->subs_srl, $oPRM->circu_srl, $oPRM->prm_srl);
        $result = sql_query($query_string);

        if($result){
            //변수선언
            $oResult = new Object();
            $result_datas = new stdClass();
            $result_fields = array(); //RRP별 출력을 위한.
            $circu_models = array(); //유통별 출력을 위한.
            $circus = array(); //각 유통들의 정보를 담음.(circu_models연계)
            $oResult->circus = array();

            while($row = sql_fetch_object($result)){
                //수익성분석이 안된 prm은 패스
                $row->profit = unserialize($row->profit);
                if(!$row->profit) continue;

                //수익데이터 정리
                $profit_models = $row->profit->models;
                unset($row->profit->models);
                foreach($profit_models as $profit){
                    $row->profit->models[$profit->no] = $profit;
                }

                //모델정보를 미리 로드해둠.
                //모델없으면 패스
                if(count(explode(",",$row->models))){
                    $query = "select * from `product_%s`
                                left join `proc_fees` on
                                (`product_%s`.`prod_subs` = `proc_fees`.`prod_subs` and `product_%s`.`category` = `proc_fees`.`category` and `proc_fees`.`product_srl` = %d)
                                left join `currency` on 
                                `currency`.`code` = `proc_fees`.`prod_currency`
                              where `no` in (%s)";
                    $query = sprintf($query,$row->table_id,$row->table_id,$row->table_id,$oPRM->product_srl,$row->models);

                    $row->models = sql_query_array($query)->data;
                    if(count($row->models)){
                        foreach($row->models as $key => $model){
                            $model->infohtml = $this->getModelHTML($model,$oPRM->field_info);
                            $row->models[$key] = $model;
                        }
                    }
                }else{
                    continue;
                }

                foreach($row->models as $no => $model){
                    //로드된 모델정보에 수익성 정보를 더해줌.
                    $model = array_merge((array)$model,(array)$row->profit->models[$model->no]);

                    if($model['rrp'] < 1) $model['rrp'] = 0;

                    //RRP순으로 저장 (전체출력을 위해)
                    if(!$result_fields[$model['rrp']]) $result_fields[$model['rrp']] = array();
                    $result_fields[$model['rrp']][$model['no']] = $model;

                    //유통별 출력을 위해 유통안에 RRP를 2차원으로 다시저장.
                    if(!$circu_models[$row->circu_srl]) $circu_models[$row->circu_srl] = array();
                    $circu_models[$row->circu_srl][$model['rrp']][$model['no']] = $model;

                    //유통정보 저장 (모델마다 저장하면 너무많아지므로 따로들고있음.)
                    if(!$oResult->circus[$row->circu_srl]){
                        $circu_info = (object) sql_fetch("select * from `circu` left join `subs` on `subs`.`subs_srl` = `circu`.`subs_srl` left join `currency` on `currency`.`code` = `subs`.`currency`  where `circu_srl` = " . $row->circu_srl);
//                        $circu_info->to_usd = $circu_;
                        $circu_info->design_id = $row->design_id;
                        $circu_info->design_parent = substr($row->design_id,0,1);

                        //유통들의 정보를 함께출력
                        $circu_info->total = $row->profit->total;

                        //유통들의 정보를 법인단위로 누적시킴
                        foreach($row->profit->total as $key => $val){
                            $result_datas->{$key} = $result_datas->{$key} + $val;
                        }

                        $circus[$row->circu_srl] = $circu_info;
                    }
                }

                krsort($result_fields);
                krsort($circu_models);
            }
//
//            if($oResult->market_size){
//                $market_size = array();
//                foreach($prm->result->market_size as $rrp => $item){
//                    $market_size[$rrp] = $item;
//                    $result_fields[$rrp] = $rrp;
//                    foreach($item as $v){
//                        $result_fields[$v->min] = $v->min;
//                    }
//                }
//                $oResult->market_size = $market_size;
//            }
//
//            if($oResult->products){
//                foreach($prm->result->products as $rrp => $item){
//                    $products[$rrp] = $item;
//                    $result_fields[$rrp] = $rrp;
//                }
//                $prm->result->products = $products;
//            }

            $oResult->result_datas = $result_datas;
            $oResult->result_fields = $result_fields;
            $oResult->circu_models = $circu_models;
            $oResult->circus = $circus;
            return $oResult;
        }else{
            return new Object(-1,"query_error");
        }
    }

    //PRM 객체생성
    function getPRM($prm_srl){
        //setPRM
        $query = "select `prm`.*, `subs`.`subs_title`, `subs`.`region`, `subs`.`currency`, `circu`.`circu_title`, `circu`.`circu_title_abb`";
        $query .= " from `prm` left join `circu` on `prm`.`circu_srl` = `circu`.`circu_srl`";
        $query .= " left join `subs` on `subs`.`subs_srl` = `prm`.`subs_srl` where `prm_srl` = ". $prm_srl;

        $oPRM = (object) sql_fetch($query);

        if(count(explode(",",$oPRM->models))){
            $oPRM->models = sql_query_array("select * from product_{$oPRM->table_id} where `no` in ({$oPRM->models})")->data;
        }else{
            $oPRM->models = array();
        }
        $oPRM->stepup = unserialize($oPRM->stepup);
        $oPRM->profit = unserialize($oPRM->profit);
        $oPRM->result = unserialize($oPRM->result);
        $profit_models = $oPRM->profit->models;
        unset($oPRM->profit->models);

        if(count($profit_models)){
            foreach($profit_models as $profit){
                $oPRM->profit->models[$profit->no] = $profit;
            }
        }

        //set proc fees
        $proc_fees = sql_query_array("select * from `proc_fees` left join `currency` on `currency`.`code` = `proc_fees`.`prod_currency` where `product_srl` = '{$oPRM->product_srl}'");
        foreach($proc_fees->data as $subs){
            $oPRM->proc_fees[$subs->prod_subs][$subs->category] = $subs;
            unset($oPRM->proc_fees[$subs->prod_subs][$subs->category]->prod_subs);
            unset($oPRM->proc_fees[$subs->prod_subs][$subs->category]->category);
            unset($oPRM->proc_fees[$subs->prod_subs][$subs->category]->product_srl);
        }

        //set sales fees
        //판매비는 법인이 어차피 PRM당 하나이므로 검색조건에 subs_srl을 같이넣어줌.
        $sale_fees = sql_query_array("select * from `sale_fees` where `product_srl` = '{$oPRM->product_srl}' and `subs_srl` = '{$oPRM->subs_srl}'");
        foreach($sale_fees->data as $subs){
            $oPRM->sale_fees[$subs->category] = $subs;
            unset($oPRM->sale_fees[$subs->category]->subs_srl);
            unset($oPRM->sale_fees[$subs->category]->category);
            unset($oPRM->sale_fees[$subs->category]->product_srl);
        }


        //set Product Info
        $query = "select `product`.*, `dept`.`dept_title` from `product` left join `dept` on `dept`.`dept_srl` = `product`.`dept_srl` where `product_srl` = ". $oPRM->product_srl;
        $oProduct = (object) sql_fetch($query);
        $oPRM->field_th = unserialize($oProduct->field_th);
        $oPRM->field_model = unserialize($oProduct->field_model);
        $oPRM->field_spec = unserialize($oProduct->field_spec);
        $oPRM->field_info = unserialize($oProduct->field_info);
        $oPRM->design_group = unserialize($oProduct->design_group);
        $oPRM->product_title = $oProduct->product_title;
        $oPRM->dept_title = $oProduct->dept_title;

        if(count($oPRM->models)){
            foreach($oPRM->models as $key => $model){
                $model->infohtml = $this->getModelHTML($model,$oPRM->field_info);
                $oPRM->models[$key] = $model;
            }
        }

        //addCurrency
        $query = "select * from `currency` where `code` = '{$oPRM->currency}'";
        $currency = (object) sql_fetch($query);
        $oPRM->currency_name = $currency->title;
        $oPRM->currency_char = $currency->char;
        $oPRM->to_usd = $currency->to_usd;

        //set Datas
        return $oPRM;
    }

}