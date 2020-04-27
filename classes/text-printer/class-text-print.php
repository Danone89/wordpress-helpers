<?php

namespace Wordpress_helpers\classes\text_printer;


class Text_Print{

    /**
     * Print width
     *
     * @var int
     */
    protected $width;
    protected $text = [];
    /**
     * Create print
     *
     * @param integer $width - set width of printed page.
     */
    function __construct($width = 400){
        $this->width = $width;
    }
    /**
     * Heading of document
     *
     * @param [type] $text
     * @return void
     */
    function heading($text){
        $this->writeln($this->aling_center(strtoupper($text)));
        $this->writeln();
        return $this;
    }

    /**
     * Write ordinary line
     *
     * @param string $text
     * @return void
     */
    function writeln($text = ''){
        $this->text[] = $text;
        return $this;
    }
    
    /**
     * Returns text in center alignment for current print width.
     *
     * @param string $text
     * @return void
     */
    function aling_center($text){
       $pad = $this->width;
        return str_pad($text,$pad," ",STR_PAD_BOTH);
    }


    /**
     * Creats table from structured data
     * 
     *
     * @param [array] $array
     * @param array $args - set params maxWidth, nobottomline, equal, notopline
     * @return void
     */
    function table(&$array,$args = []){
        $table = new ArrayToTextTable($array);
        $table->showHeaders(true);
        $maxWidth = empty($args['maxWidth']) ? ceil($this->width/count($array[0]))+1 : $args['maxWidth'];
        $table->setMaxWidth($maxWidth);
        if(isset($args['equal']))
            $table->setMinWidth($args['equal']);
        if(!empty($args['nobottomline']) && $args['nobottomline'] > 0){
            $table->noBottomLine();
        }
        if(!empty($args['notopline']) && $args['notopline'] > 0){
            $table->noTopLine();
        }
        $this->text[] = $table->render(true);
        return $this;
    }
    
    /**
     * Prints line through whole document
     *
     * @param string $line - printed character
     * @return self
     */
    function line($line = '-'){
        $buff = '';
        for($i=0;$i<$this->width;$i++){
            $buff .= $line;
        }
        $this->writeln($buff);
        return $this;
    }
    
    /**
     * Get print page
     *
     * @return void
     */
    function toString(){
        return implode("\r\n",$this->text);
    }

}


