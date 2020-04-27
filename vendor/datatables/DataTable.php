<?php

namespace pix\datatables;

//@todo  refactor for reusing outside WP. Need to create separate clas WpDatatable with getScriptsArgs overloaded to use filters
class DataTable{
    /**
     * Table internal ID
     */
    var $id;
    var $class = 'table table-bordered table-striped';
    var $headers;
    var $body = [];
    var $options; 
    protected $scriptArgs = [
        'serverSide'=>'',
        'buttons' => '',
        'columns' => '',
        'language'=> 'DataTablesPL',
        'order' => [[0,'desc']],
        'dom'=>"<'row'<'col-sm-12 col-md-6'l><'col-sm-12 col-md-6'f>>".
                "<'row'<'col-sm-12'tr>>".
                "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
        'mark' => true,
        'select' => true,
        'processing' => true,
    ];
    /*      'ajax' => [
        'url' => '',
        'dataSrc'=>"",
        'method'=>'rest'
    ],*/

    function __construct($id = '')
    {   
        if(empty($id)){
            $id = 'l'. rand(100,200);
        }
        $this->id = $id;
        
    }

    function setAttributes($attr,$val){
        $this->scriptArgs[$attr] = $val;
    }


    /**
     * Undocumented function
     *
     * 
     * @return void
     */
    function formatColumns(){
        
    }
    /**
     * Undocumented function
     *
     * @param [type] $url
     * @param boolean $serverSide
     * @return void
     */
    function setDataSource($url,bool $serverSide = false,$data_src = ''){
        if(!$serverSide){
            $this->scriptArgs['ajax'] = [
                'url'=>       $url,
                'data_src' => (string) $data_src
            ];
     
        }
        $this->scriptArgs['serverSide'] = $serverSide;
      

    }
    /**
     * Undocumented function
     *
     * @param array $body
     * @param boolean $append
     * @return void
     */
    function setData(array $body,bool $append = false){
        $this->body = $append ? $this->body + $body : $body;
    }

    /**
     * Undocumented function
     *
     * @return string
     */
    function tableHTML(){
        $ret = sprintf('<table class="%s" id="%s" data-href="%s">',$this->class,$this->id,$this->scriptArgs['ajax']['url']??'');
        $ret .='<thead class="thead-dark"><tr>';
        foreach($this->headers as $column){
            $ret .= '<th>'.$column.'</th>';
        }
        $ret  .= '</tr>';
        $ret .= '</thead>';
        //static
        $ret .= '<tbody>';
        $ret .= '</tbody>';
        $ret .= '</table>';
   
        $this->registerScripts();
        return $ret;
    }
    function registerScripts(){
        wp_localize_script('datatables', $this->scriptsId(), $this->getScriptsArgs());
        wp_add_inline_script(
            'datatables',
            apply_filters('datatables_ini_' . $this->scriptsId(), sprintf('jQuery("document").ready(function($){
            %2$s.object = initTable(%2$s);
        });', $this->id,$this->scriptsId())));
    }
    function getScriptsArgs(){
        foreach($this->scriptArgs as $k=>$v){
            if($v === null) unset($this->scriptArgs['k']);
        }
        return apply_filters('datatable_params',[
            'table' => $this->scriptArgs,
            'selector' =>  '#'.$this->id
        ],$this->id);
        
    }
    function scriptsId(){
        return $this->id . 'Table';
    }

}