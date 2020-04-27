<?php

namespace pix\datatables;

/**
 * Functions as director
 */
class DatatableShortcode {


    static function buildTable($args = [],$content = ''){
        $params = shortcode_atts( array( 
            'args' => '',
            'class' => 'pix\datatables\GenericTable',
            'columns' => '',//comma
            'serverSide'=>true,
            'ajax' => '',
            'id'=>'list',
        ), $args, 'datatable' );
        if(!$params['id']) return 'brak paramteru ID';
        $idParams = $params['id'].'Table';
        $columns = $params['columns'] ? explode(',',$params['columns']) : $this->getColumnsFromClass($params['class']);
        if(!$columns){
            return current_user_can('manage_options') ?'Brak nagłówków tabelii' : 'Wystąpiły problemy techniczne.';
        }
        $tableBuilder = new \DatatableBuilder($params['id']);
        $tableBuilder->setTableData($content);
        $tableBuilder->setColumns($columns);
        $tableBuilder->setAjax($params['ajax'],$params['serverSide']);

        $Table = $tableBuilder->getTable();
    
        $script_params = [
            'table' => $Table->getScriptParams(),
            'nonce' => wp_create_nonce( 'wp_rest' )
        ];
        wp_localize_script('datatables',$idParams,$script_params);       
        wp_add_inline_script('datatables',sprintf('jQuery("document").ready(function($){
            %2$s.object = initTable(%2$s);
        });',$params['id'],$idParams));



        return $Table->showTable();
    }


    static protected function getColumnsFromClass($class){
        if(!class_exists($class) or !($class instanceof DataTableModel)){
            return;

        }
        $params = $class::tableColumns();
     
    }



}
