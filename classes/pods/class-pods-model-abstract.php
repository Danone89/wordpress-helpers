<?php

namespace Wordpress_helpers\classes\pods;

use \Iterator;
use bss\users\Employee;

abstract class Pod_Model_Abstract
{
    /**
     * Object id
     *
     * @var integer
     */
    protected $id = 0;

    protected $Query;
    protected $Pod;
    protected $_Related;
    protected $data;
    abstract  static function pod(); //: string;

    public function __construct($id = 0)
    {
        if ($id) {
            $this->Pod = pods(get_class($this)::pod(), $id);
            $this->init();
            if ($this->exists()) {
                $this->id = $this->Pod->raw('ID');
            }
        } else {
            $this->Pod = pods(get_class($this)::pod());
        }
    }

    public function __get($name)
    {
        return $this->Pod->display($name);
    }
    /**
     * Get value used for database storage 
     *
     * @param [type] $name
     * @return void
     */
    public function get_raw_value($name)
    {
        return $this->Pod->raw($name);
    }

    public function __set($name, $value)
    {
        if (method_exists($this,'set_' . $name)) {
            list($name, $value) = $this->{"set_$name"}($value);
        }
        $this->data[$name] = $value;
        $this->$name = $value;
    }


    public function display_name()
    {
        return isset($this->title) ? $this->title : $this->name;
    }


    protected function init()
    {
        foreach ((new \ReflectionObject($this))->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop) {
            $this->data[$prop->name] = $this->Pod->field($prop->name);
        
        }
    }

    /**
     * Saves model data
     *
     * @return id - Object Id
     */
    public function save($update = true, $overrides = []): int
    {
        if (!$this->data['author']) {
            global $current_user;
            $this->author = $current_user->ID;
        }
        $data = $this->data;
        /*foreach ((new \ReflectionObject($this))->getProperties(\ReflectionProperty::IS_PUBLIC) as $prop) {
            $data[$prop->name] = $this->{$prop->name};
        }*/

        if (is_array($overrides)) {
            foreach ($overrides as $k => $v) {
                $data[$k] = $v;
            }
        }

        if ($this->id > 0) {
            ///$data['created']=  date('Y-m-d H:i:s');
            $id = $this->Pod->save($data);
        } else {
            // $data['modified'] =  date('Y-m-d H:i:s');
            $id = $this->Pod->add($data);
        }
        $this->id = $id;
        return intval($id);
    }

    public function exists(): bool
    {
        return $this->Pod && $this->Pod->display('ID') > 0;
    }

    /**
     * Fetches data from db
     * @todo optimalization needed
     * @param array $where string
     * @param array $options array
     * @return object
     */
    public static function findOne(string $where = '')
    {
        $class = get_called_class();
        $pod = $class::pod();

        if (intval($where) > 0) {
            $Pod = new $class((int) $where);
            return $Pod->id > 0 ? $Pod : null;
        } else {
            $args = [];
            $args['where'] = $where;
        }

        $Pod = pods($pod, $args);

        if ($Pod->fetch())
            return new $class($Pod);

        return null;
    }

    /**
     * Fetches data from db
     * @todo optimalization needed
     * @param array $where string
     * @param array $options array
     * @return object
     */

    public static function find(string $where = '', array $options = [])
    {
        $class = get_called_class();
        $pod = $class::pod();

        if (empty($options['limit'])) {
            $options['limit'] = 100;
        }

        $args = $options;
        $args['where'] = $where ? $where : ' t.post_status = "publish"'; //.'and t.post_status LIKE "%"';
        //self::$Query = pods($pod)->find($args);
        return new Pod_Result($pod, $args, $class);
    }
}
//@todo need place for that
class Pod_Result implements \IteratorAggregate
{

    protected $Query;
    protected $class;
    function __construct($pod, $args, $class)
    {
        $this->Query = pods($pod)->find($args);
        $this->class = $class;
    }

    function get_result()
    {
        $class = $this->class;
        while (($row = $this->Query->fetch()) !== false) {

            $Object = new $class(empty($row['ID']) ? $row['term_id'] : $row['ID'] );
            if(!$Object->exists()) continue;
            yield $Object;
        }
    }

    function getIterator()
    {
        return $this->get_result();
    }

    public function total()
    {
        return $this->Query->total_found();
    }
}
