<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace PIX\Model;

/**
 * Description of Model
 *
 * @author bosnj
 */
abstract class Model {

    function __construct($id = '', $array = []) {

        global $db;
        foreach ($array as $k => $v) {
            if (isset($this->{$k}))
                $this->{$k} = $v;
        }
        $this->data = [];
        if (!$id)
            return;
        $query = "SELECT * FROM " . static::Table . " WHERE " . static::PK . "='$id'";
        $this->data = $db->query($query,\PDO::FETCH_ASSOC);
        if ($this->data->rowCount() !== 1) {
            throw new NotFoundException();
        }
        foreach ($this->data->fetch() as $k => $v) {
            $this->{$k} = $v;
        }
    }

    function save() {
        $this->_save();
    }

    /**
     * Retrun array of strings with labels for columns
     * @return array
     */
    function __get($name) {
        if (isset($this->{$name}))
            return $this->{$name};
    }

    function columnNames($transalted = []) {
        $columns = [];
        $translate = [];
        if (is_array($transalted))
            $translate = $transalted;

        foreach ((new \ReflectionObject($this))->getProperties(\ReflectionProperty::IS_PUBLIC) as $k) {
            if (!empty($translate) && isset($translate[$k->name])) {
                $columns[] = $translate[$k->name];
            } else {
                $columns[] = $k->name;
            }
        }
        return $columns;
    }

    protected function getColumnNames($all = false) {
        $list = [];
        if ($all) {
            $list[] = static::PK;
        }
        foreach ((new \ReflectionObject($this))->getProperties(\ReflectionProperty::IS_PUBLIC) as $k) {
            $list[] = $k->getName();
        }

        return $list;
    }

    protected function _save() {
        global $db;
        if ($this->{static::PK} > 0) {
            $sql = $this->getUpdateSQL();
        } else {
            $sql = $this->getInsertSQL();
            $insert = 1;
        }
        $stmt = $db->prepare($sql);
        foreach ($this->getColumnNames() as $column)
            $stmt->bindValue(':' . $column, $this->{$column});

        $stmt->execute();
        if ($this->{static::PK} == 0)
            $this->{static::PK} = $db->lastInsertId();
        return $this->{static::PK};
    }

    protected function getUpdateSQL() {
        $update = '';
        $update = "UPDATE " . static::Table . " SET ";
        foreach ($this->getColumnNames() as $l) {
            $update .= ":$l = :$l";
        }
        $update .= ' WHERE ' . static::PK . '=' . $this->{self::PK};
        return $update;
    }

    protected function getInsertSQL() {
        $columns = $this->getColumnNames();
        $insert = "INSERT INTO " . static::Table;
        $insert .= ' (' . implode(',', $columns) . ')';
        $insert .= ' VALUES (';
        foreach ($this->getColumnNames() as $l) {
            $insert .= ":$l,";
        }
        $insert = mb_substr($insert, 0, -1);
        $insert .= ')';
        return $insert;
    }

    function delete() {
        $this->deleted = 1;
        return $this->save();
    }

    function toArray() {
        return (array) $this;
    }

}
