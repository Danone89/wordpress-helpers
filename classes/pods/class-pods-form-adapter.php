<?php


namespace pix\pods\helpers;

use Form_Director_Interface;
use pix\helpers\Bootstrap_Form_Builder;
use Pods;
use PodsForm;

class Pods_Form_Adapter implements Form_Director_Interface
{

    // actual builder for pods data form
    protected $builder;

    protected $Pod;

    function __construct($id, $loader = null)
    {
        $this->Pod = new pods($id);
        $this->builder = new Bootstrap_Form_Builder($this->Pod->pod, $loader);
    }
    function Open()
    {
        $this->builder->open();
        $uid = 'user_' . get_current_user_id();
        //$nonce = wp_create_nonce('pods_form_' . $this->pod->pod . '_' . $uid . '_' . $pod->id() . '_' . $uri_hash . '_' . $field_hash);
        //$uri_hash = wp_create_nonce('pods_uri_' . $_SERVER['REQUEST_URI']);
        //$field_hash = wp_create_nonce('pods_fields_' . implode(',', array_keys($submittable_fields)));
        
        //PodsForm::field('action', 'pods_admin', 'hidden');
        //PodsForm::field('method', 'process_form', 'hidden');
        $this->builder->Hidden('do', (0 < $this->pod->id() ? 'save' : 'create'));
        //$this->builder->Hidden('_pods_nonce', $nonce);
        //PodsForm::field('_pods_pod', $this->pod->pod, 'hidden');
        $this->builder->Hidden('_pods_id', $this->pod->id(),);
        //$this->builder->Hidden('_pods_uri', $uri_hash);
        //PodsForm::field('_pods_form', implode(',', array_keys($submittable_fields)), 'hidden');
        //PodsForm::field('_pods_location', $_SERVER['REQUEST_URI'], 'hidden');
    }
    function validate()
    {
        // unset fields
        $fields = $this->pods->fields();
        foreach ($fields as $k => $field) {

            // Make sure all required array keys exist.
            $field = wp_parse_args($field, array(
                'name' => '',
                'type' => '',
                'label' => '',
                'help' => '',
                'options' => array(),
            ));
            $fields[$k] = $field;

            if (in_array($field['name'], array('created', 'modified'), true)) {
                unset($fields[$k]);
            } elseif (false === PodsForm::permission($field['type'], $field['name'], $field['options'], $fields, $pod, $pod->id())) {
                if (pods_var('hidden', $field['options'], false)) {
                    $fields[$k]['type'] = 'hidden';
                } elseif (pods_var('read_only', $field['options'], false)) {
                    $fields[$k]['readonly'] = true;
                } else {
                    unset($fields[$k]);
                }
            } elseif (!pods_has_permissions($field['options'])) {
                if (pods_var('hidden', $field['options'], false)) {
                    $fields[$k]['type'] = 'hidden';
                } elseif (pods_var('read_only', $field['options'], false)) {
                    $fields[$k]['readonly'] = true;
                }
            }
        }
    }
}
