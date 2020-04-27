<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PIX\Model;

/**
 * Description of ResultContainer
 *
 * @author bosnj
 */
abstract class ResultContainer implements \ArrayAccess {

    protected $container = [];
    var $query = '*';
    var $join;
    var $count_join = '';
    var $where = 'deleted = 0';
    var $page_size = PAGE_SIZE;
    var $totalCount = 0;
    var $orderby = '';
    
    
    /*
     * Wrap results in objects
     */
    var $as_object = false;

    /* Array access */

    public function offsetExists($offset) {
        return isset($this->container[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->container[$offset]);
    }

    public function offsetSet($offset, $value) {
        return;
        $this->container[$offset] = $value;
    }

    public function offsetGet($offset) {
        return isset($this->container[$offset]) ? $this->container[$offset] : null;
    }

    /* Array access */

    abstract function whereFind();

    abstract function whereList();

    abstract function wrapResult($data);

    /**
     * 
     * @global type $db
     * @return int row count
     */
    public function totalCount() {
        global $db;
        if ($this->totalCount == 0) {
            $result = $db->query("SELECT COUNT(*) as num FROM $this->from $this->count_join  WHERE {$this->where} ");
            $this->totalCount = $result->fetchColumn();
        }
        return $this->totalCount;
    }

    function find($search, int $page = 1, $orderby = '') {
        global $db;
        $this->where = $this->whereFind();
        $order = isset($order) ? $order : $this->orderby;
        $offset = ($page - 1) * $this->page_size;

        $stmt = $db->prepare("SET @term = :term");
        $stmt->bindValue(":term", "$search%", \PDO::PARAM_STR);
        $stmt->execute();

        $query = $db->prepare("SELECT * from {$this->from}  {$this->join} where  $this->where order by :o DESC LIMIT $offset,  $this->page_size");
        $query->bindValue(':o', $orderby);
        $query->execute();
        foreach ($query->fetchAll(\PDO::FETCH_ASSOC) as $v) {
            $this->container[] = $this->wrapResult($v);
        }
        return $this;
    }

    function fetch($page = 0, $order = '', $object = false) {
        global $db;
        $order = $order ? $order : $this->orderby;
        $this->where = $this->whereList();

        if ($page > 0) {
            $query = sprintf("SELECT $this->query from " . $this->from . " %s where  %s ORDER BY %s Limit %s,%s", $this->join, $this->where, $order, ($page - 1) * $this->page_size, $this->page_size);
        } else {
            $query = sprintf("SELECT $this->query from " . $this->from . " %s where  %s ORDER BY %s ", $this->join, $this->where, $order);
        }


        $query = $db->prepare($query);
        $result = $query->execute();
        foreach ($query->fetchAll(\PDO::FETCH_ASSOC) as $v) {
            $this->container[] = $this->wrapResult($v);
        }
        return $this;
    }

    function toArray() {
        return $this->container;
    }

    function alterResult($array) {
        $this->container = $array;
    }

}
