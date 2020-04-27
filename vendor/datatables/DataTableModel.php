<?php



namespace pix\datatables; 


interface DataTableModel{

    /** 
     * Return array of api_field=>label field 
     */
    static function tableColumns(string $view = 'default');

    /**
     * Datasource configuration array
     * URL: string
     * ServerSide: bool
     * @return array url,ServerSide
     */
     static function datasource($context = '');



}