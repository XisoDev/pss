<?php

class settingsModel{

    function init(){

    }

    function getProcfeesField(){
        return array("labor_var","labor_fix","mfg_var","mfg_fix");
    }
    function getSalesfeesField(){
        return array("inland_cost","amount_var","amount_fix","admin_expenses","rnd","service_var","service_fix","transportation");
    }

    function getCategoryList($table_id){
        $query = "SELECT `category` FROM `product_{$table_id}` group by `category`";
        $data = sql_query_array($query)->data;
        $ret_array = array();
        foreach($data as $category){
            $ret_array[] = $category->category;
        }
        return $ret_array;
    }
}