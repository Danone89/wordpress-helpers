<?php
/**
 * Array to Text Table Generation Class
 *
 * @author Tony Landis <tony@tonylandis.com>
 * @link http://www.tonylandis.com/
 * @copyright Copyright (C) 2006-2009 Tony Landis
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
namespace pix\helpers\TextPrinter;

class ArrayToTextTable
{
    /** 
     * @var array The array for processing
     */
    private $rows;
    /** 
     * @var int The column width settings
     */
    private $cs = array();
    /**
     * @var int The Row lines settings
     */
    private $rs = array();
    /**
     * @var int The Column index of keys
     */
    private $keys = array();
    /**
     * @var int Max Column Height (returns)
     */
    private $mH = 2;
    /**
     * @var int Max Row Width (chars)
     */
    private $mW = 30;
    private $head  = false;
    private $pcen  = "+";
    private $prow  = "-";
    private $pcol  = "|";
    /**
     *
     * @var bool  Display bottom table line
     */
    private $bottomLine = true;
    /**
     *
     * @var bool  Display top table line
     */
    private $topLine = true;
    
    /**
     * @var integer Minimal character width
     */
    private $minimalWidth = 0;

    /** Prepare array into textual format
     *
     * @param array $rows The input array
     * @param bool $head Show heading
     * @param int $maxWidth Max Column Height (returns)
     * @param int $maxHeight Max Row Width (chars)
     */
    public function __construct($rows)
    {
        $this->rows =& $rows;
        $this->cs=array();
        $this->rs=array();

        if(!$xc = count($this->rows)) return false; 
        $this->keys = array_keys($this->rows[0]);
        $columns = count($this->keys);
        for($x=0; $x<$xc; $x++)
            for($y=0; $y<$columns; $y++)    
                $this->setMax($x, $y, $this->rows[$x][$this->keys[$y]]);
    }
    
    /**
     * Show the headers using the key values of the array for the titles
     * 
     * @param bool $bool
     */
    public function showHeaders($bool)
    {
       if($bool) $this->setHeading(); 
       return $this;
    } 
    
    /**
     * Set the maximum width (number of characters) per column before truncating
     * 
     * @param int $maxWidth
     */
    public function setMaxWidth($maxWidth)
    {
        $this->mW = (int) $maxWidth;
        return $this;
    }
       
    /**
     * Set the maximum width (number of characters) per column before truncating
     * 
     * @param int $maxWidth
     */
    public function setMinWidth($minWidth)
    {
        $this->minimalWidth = $minWidth;
        $columns = count($this->keys);
        $xc = count($this->rows);
        for($x=0; $x<$xc; $x++)
            for($y=0; $y<$columns; $y++)    
                $this->setMax($x, $y, $this->rows[$x][$this->keys[$y]]);

        return $this;
    }
    /**
     * Set the maximum height (number of lines) per row before truncating
     * 
     * @param int $maxHeight
     */
    public function setMaxHeight($maxHeight)
    {
        $this->mH = (int) $maxHeight;
        return $this;
    }

    public function noBottomLine(){
        $this->bottomLine = false;
        return $this;

    }

    public function noTopLine(){
        $this->topLine = false;
        return $this;

    }
    
    /**
     * Prints the data to a text table
     *
     * @param bool $return Set to 'true' to return text rather than printing
     * @return mixed
     */
    public function render($return=false)
    {
        if($return) ob_start(); 
        if($this->topLine)  $this->printLine();
        $this->printHeading();
        
        $rc = count($this->rows);
        for($i=0; $i<$rc; $i++)
            $this->printRow($i);
        
        
        if($this->bottomLine) $this->printLine(false);
        if($return) {
            $contents = ob_get_contents();
            ob_end_clean();
            return $contents;
        }
    }

    private function setHeading()
    {
        $data = array();  
   
        foreach($this->keys as $colKey => $value)
        { 
            $this->setMax(false, $colKey, $value);
            $data[$colKey] = strtoupper($value);
        }

        if(!is_array($data)) return false;
        $this->head = $data;
        return $this;

    }
    private function printLine($nl=true)
    {
        print $this->pcen;
        foreach($this->cs as $key => $val)
            print $this->prow .
                str_pad('', $val, $this->prow, STR_PAD_RIGHT) .
                $this->prow .
                $this->pcen;
        if($nl) print "\r\n";
        return $this;

    }
    private function printHeading()
    {
        if(!is_array($this->head)) return false;
        print $this->pcol;
        foreach($this->cs as $key => $val)
            print ' '.
            mb_str_pad($this->head[$key], $val, ' ', STR_PAD_BOTH) .
                ' ' .
                $this->pcol;
        print "\r\n";
        $this->printLine();
        return $this;

    }
    private function printRow($rowKey)
    {
        // loop through each line
        for($line=1; $line <= $this->rs[$rowKey]; $line++)
        {
            print $this->pcol;  
            for($colKey=0; $colKey < count($this->keys); $colKey++)
            { 
                print " ";
                print mb_str_pad(substr($this->rows[$rowKey][$this->keys[$colKey]], ($this->mW * ($line-1)), $this->mW), $this->cs[$colKey], ' ', STR_PAD_RIGHT);
                print " " . $this->pcol;          
            }  
            print  "\r\n";
        }
    }
    private function setMax($rowKey, $colKey, &$colVal)
    { 
        $w =  mb_strlen($colVal);
        $h = 1;
        if($w > $this->mW)
        {
            $h = ceil($w % $this->mW);
            if($h > $this->mH) $h=$this->mH;
            $w = $this->mW;
        }else if($this->minimalWidth > 0){
            $w = $this->minimalWidth;
        }
 
        if(!isset($this->cs[$colKey]) || $this->cs[$colKey] < $w)
            $this->cs[$colKey] = $w;
        if($rowKey !== false && (!isset($this->rs[$rowKey]) || $this->rs[$rowKey] < $h))
            $this->rs[$rowKey] = $h;

        return $this;

    }
    
}
/**
 * https://stackoverflow.com/questions/11871811/special-characters-throwing-off-str-pad-in-php
 *
 * @param [type] $input
 * @param [type] $pad_length
 * @param string $pad_string
 * @param [type] $pad_type
 * @return void
 */
function mb_str_pad( $input, $pad_length, $pad_string = ' ', $pad_type = STR_PAD_RIGHT)
{
    $diff = strlen( $input ) - mb_strlen( $input );
    return str_pad( $input, $pad_length + $diff, $pad_string, $pad_type );
}
