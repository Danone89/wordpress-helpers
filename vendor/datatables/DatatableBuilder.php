<?php

namespace pix\datatables;

/**
 * Builder Pattern
 */
class DatatableBuilder
{

    protected $table;
    protected $modules;

    function __construct($id)
    {
        $this->table = new DataTable($id);
    }
    /**
     * Undocumented function
     *
     * @param array $columns
     * @return void
     */
    function setTableHeaders($columns)
    {
        $data = array_keys($columns);
        $label = array_values($columns);
        $this->table->setAttributes('columns', array_map('pix\datatables\columnParameter', $data));
        $this->table->headers =  $label;
    }
    /**
     * Row grouping
     *
     * @param string $row - valid row data key.
     * @return void
     */
    function setRowGroup($row)
    {
        $this->table->setAttributes('rowGroup', ['dataSrc' => $row]);
    }
    function setOrder($ordering)
    {
        $this->table->setAttributes('order', [$ordering]);
    }
    function setTableData()
    {
    }

    function setAjax($config)
    {
        $this->table->setAttributes('ajax', $config);
    }
    function setServerSide()
    {
        $this->table->setAttributes('serverSide', true);
        $this->table->setAttributes('lengthMenu', [[10, 25, 50, 100, -1], [10, 25, 50, 100, "Wszystko"]]);
    }
    function setSearchable($val = true)
    {
        $this->table->setAttributes('searching', $val);
    }
    function setPaging($val = true)
    {
        $this->table->setAttributes('paging', $val);
    }
    function setPrintable($methods = ['csvHtml5', 'excelHtml5', ['extend' => 'colvis', 'text' => 'Kolumny']])
    {
        $dom = "<'row'<'col-sm-12 col-md-4'B><'col-sm-12 col-md-4'l><'col-sm-12 col-md-4'f>>" .
            "<'row'<'col-sm-12'tr>>" .
            "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>";
        $this->table->setAttributes('dom', $dom);

        $methods[] = [
            'extend' => 'print',
            'exportOptions' => [
                'columns' => ':visible'
            ],
            'text' => 'Drukuj'
        ];
        $this->table->setAttributes(
            'buttons',
            $methods

        );
    }

    function &getTable()
    {


        return $this->table;
    }
}

function columnParameter($elem)
{
    if (is_array($elem)) return $elem;
    $name = trim($elem);
    return ['data' => $name];
}
