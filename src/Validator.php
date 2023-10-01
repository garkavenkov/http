<?php

namespace Http;

use Registry\Registry;

class Validator
{
    /**
     * Available validation rules
     *
     * @var array
     */
    private $available_rules = array();
    
    /**
     * Fill validation rules array     
     */  
    public function __construct()
    {
        $this->available_rules = array(    
            'required'  =>  [
                'handler'   =>  function(...$argv) { return !empty($argv[0]); },
                'error'     =>  'Field \':field:\' is required',
                'params'    =>  []
            ],
            'min'       =>  [ 
                'handler'   =>  function(...$argv) { return strlen($argv[0]) >= (int) $argv[1]; }, 
                'error'     =>  'Value for \':field:\' is must be at least :min: symbols' ,
                'params'    =>  array('name' => ['min']),
            ],
            'unique'    =>  [
                'handler'   =>  function(...$argv) {
                    [$table, $field] = explode('.', $argv[1], 2);                    
                    $db = Registry::get('db');                    
                    $sql = "SELECT * FROM `$table` WHERE `$field` = '{$argv[0]}'";                    
                    return count($db->query($sql)->getRows()) > 0 ? false : true;                    
                }, 
                'error'     =>  'Value is already exists in table \':table:\' for field \':field:\'',
                'params'    =>  array('separator' => '.', 'name' => ['table', 'field'])
            ]            
        );
    }

    /**
     * Check if rule is present in validation rules
     *
     * @param string $rule  Rule name
     * @return boolean
     */
    public function ruleExists(string $rule): bool
    {
        return isset($this->available_rules[$rule]);
    }

    /**
     * Return handler for rule
     *
     * @param string $rule  Rule name
     * @return callable|Exception
     */
    public function ruleHandler(string $rule)
    {        
        if (isset($this->available_rules[$rule])) {
            if (isset($this->available_rules[$rule]['handler'])) {
                if(is_callable($this->available_rules[$rule]['handler']))
                    return $this->available_rules[$rule]['handler'];
            } else {
                throw new \Exception("Handler for $rule does not set", 500);
            }
        } else {
            throw new \Exception("Validator does not have $rule", 500);
        }
    }

    /**
     * Return available rules names
     *
     * @return array
     */
    public function rules(): array
    {        
        return array_keys($this->available_rules);
    }

    /**
     * Generate error if rule did not pass validation
     *
     * @param string $field         Form field
     * @param string $rule_name     Rule's name
     * @param string $rule_param    Rule's param (i.e. length, field name, etc.)
     * @param string $message       Error message instead of rule's error message
     * @return string
     */    
    public function ruleError(string $field, string $rule_name, string $rule_param, string $message = ''): string
    {        
        if ($message == '') {
            $message = $this->available_rules[$rule_name]['error'];
        }
     
        if (isset($this->available_rules[$rule_name]['params'])) {
            $rule_params = [];
            if (isset($this->available_rules[$rule_name]['params']['separator'])) {
                $rule_params = explode($this->available_rules[$rule_name]['params']['separator'], $rule_param);
            } else {
                $rule_params = explode(' ', $rule_param);
            }

            if (isset($this->available_rules[$rule_name]['params']['name'])) {
                $params = array_map(
                    function($p) {                
                        return '/:' . $p . ':/';
                }, $this->available_rules[$rule_name]['params']['name']);
        
                $message = preg_replace($params, $rule_params, $message);
            }
        }        
        return preg_replace('/:field:/', $field, $message);        
    }
}