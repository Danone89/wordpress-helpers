<?php

namespace pix\datatables;


class GenericTable implements TableDirector
{

    protected $tableBuilder;
    protected $error;
    protected $Model = null;
    protected $params;

    function __construct($id = '', $params = [])
    {
        $this->tableBuilder = new DatatableBuilder($id);
        $this->params = $params;
    }
    function buildTable()
    {

        $this->tableBuilder->setTableHeaders($this->params['columns']);
        if(!empty($this->params['ajax']))
            $this->tableBuilder->setAjax(['url' => $this->params['ajax'], 'dataType' => $this->params['datatype']]);
        $this->process_params();
        //return $this->getTable();

    }

    function buildTableFromModel($model)
    {
        $context = isset($this->params['context']) ? $this->params['context'] : 'default';
        if ($this->setModel($model)) {
            $this->tableBuilder->setTableHeaders($this->Model::tableColumns($context));
            $this->tableBuilder->setAjax($this->Model::datasource($context));
            $this->process_params();

            //return $this->getTable();
        } else {
            $this->error = 'Nie prawidłowy model ' . $model;
        }
    }

    protected function process_params()
    {

        if (!empty($this->params['printable']) && $this->params['printable'])
            $this->tableBuilder->setPrintable();
        if ($this->params['nosearch']) {
            $this->tableBuilder->setSearchable(false);
        }
        if ($this->params['nopaging']) {
            $this->tableBuilder->setPaging(false);
        }
        if ($this->params['serverside']) {
            $this->tableBuilder->setServerSide();
        }
        if($this->params['rowgroup']){
            $this->tableBuilder->setRowGroup($this->params['rowgroup']);
        }
    }
    function setModel($Model)
    {
        if (!class_exists($Model)) {


            return false;
        }
        $implements  = class_implements($Model);
        if (empty($implements['pix\datatables\DataTableModel'])) {

            return false;
        }
        $this->Model = $Model;
        return true;
    }
    function getTable()
    {
        if ($this->error) {
            throw new \Exception($this->error);
        }
        return $this->tableBuilder->getTable();
    }
}
