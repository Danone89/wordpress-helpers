<?php

namespace pix\helpers;


abstract class FileParser
{

    protected $file_path;
    protected $file;
    protected $delimiter;
    protected $configuration;
    protected $line_number = 0;

    function __construct(String $file_path, $conf = [])
    {
        $this->file_path = $file_path;
        $this->configuration = $conf;
        //  if(empty($this->delimiter))
        //      $this->delimiter = $this->configuration['delimiter']??';';
        $this->init();
    }

    protected function init()
    {

        $this->file = fopen($this->file_path, 'r');

        if (!is_resource($this->file))
            throw new \Exception('brak takiego pliku');
    }

    abstract public function parse();

    protected function getLine($raw = false)
    {
        return fgets($this->file);
    }

    protected function rewind()
    {
        $this->line_number = 0;
        fseek($this->file, 0);
    }

    protected function end()
    {
        fclose($this->file);
    }
    function getConf($name = '')
    {
        if ($name == 'all') {
            return $this->configuration;
        }
        if (isset($this->configuration[$name]) && !empty($this->configuration[$name]))
            return $this->configuration[$name];
    }

    /**
     * Format text represented number to floatval precision 2
     *
     * @param [string] $number
     * @return float
     */
    protected function format_number($number): float
    {
        $number =  floatval(str_replace(' ', '', $number));
        return  round($number, 2);
    }
}
function removehypens($element){
    return str_replace(['"',"\n","\r"],['','',''],$element);
}