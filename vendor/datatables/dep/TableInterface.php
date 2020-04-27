<?php

/**
 * Description of TableController
 *
 * @author bosnj
 */

namespace pix\datatables;

abstract class TableInterface {

    protected $columnNames = [];
    protected $data = [];

    /**
     *
     * @var array - params from RPC call
     */
    protected $params = [];

    /**
     * Container for Compatiblie Model class
     * @var object
     */
    protected $Model;

    /**
     * Translation array
     * @var array 
     */
    protected $translate;

    abstract static function rpcName();

    abstract protected function getData($page, $lenght, $order = '', $search = '');

    function apiurl() {
        return $this->rpcName() . '.fetchJsonData';
    }


    function tableColumns() {
        //reflection from model?
        return $this->columnNames;
    }

    function fetch($columns, $draw, $length, $start, $search, $order, $params = []) {
        $start = $start == 0 ? 1 : (int) $start;
        $column = array_keys($this->tableColumns());
        $order = $column[0] . ' ' . ($order[0]['dir'] == 'asc' ? 'asc' : 'desc');
        $page = intval(abs($start / $length)) + 1;

        $this->params = $params;
        $result = $this->getData($page, $length, $order, $search['value']);
        return ['data' => $result->toArray(), 'draw' => (int) $draw, 'recordsTotal' => $result->totalCount(), "recordsFiltered" => $result->totalCount()];
    }



}
