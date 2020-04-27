<?php


interface Form_Director_Interface{

    function Open();
    function Close($buttons = false);
    function validate();
}