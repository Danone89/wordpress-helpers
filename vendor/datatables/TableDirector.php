<?php

namespace pix\datatables;


interface TableDirector{
    
    function __construct($id,$params);
    function buildTable();
    function getTable();
}