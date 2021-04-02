<?php

abstract class Form
{
    const MODEL = null;
    private $instance;
    public $fields;
    private $data;


    public function __construct($data=[], $instance=null)
    {
        $class = get_called_class();
        if(!$class::MODEL){ return; }
        $this->instance = $instance;
        $this->data = $data;
        $model = $class::MODEL;
        $this->fields = get_object_vars(new $model());
        foreach($this->fields as $key => $val){ $this->attributes[$key] = ''; }
        if($this->instance){
            foreach(get_object_vars($this->instance) as $key => $val){
                $this->fields[$key]->add_attr("value='$val'");
            }
        }
        if(method_exists($class, 'custom')){ $this->custom(); }
    }


    public function is_valid()
    {
        $errors = [];
        foreach($this->fields as $field)
        {
            if(!isset($this->data[$field->name]) && isset($field->settings['required']) && $field->settings['required'] == false){
                continue;
            }
            $val = $this->data[$field->name];
            if(isset($field->settings['unique']) && $field->settings['unique'] == true){
                $class = get_called_class();
                $model = $class::MODEL;
                $objects = $model::filter([ [$field->name, '=', $val] ]);
                if(count($objects) > 0){
                    array_push($errors, "$field->name is not unique");
                }
            }
            if((!isset($field->settings['required']) || $field->settings['required'] == true) && $val == ''){ 
                return false; 
            }
            if(isset($field->settings['max']) && strlen($val) > $field->settings['max']){ 
                array_push($errors, "$field->name can't be longer than ".$field->settings['max']); 
            }
            if(isset($field->settings['min']) && strlen($val) < $field->settings['min']){
                array_push($errors, "$field->name can't be shorter than ".$field->settings['min']); 
            }
        }

        if(!isset($_SESSION['messages'])){ $_SESSION['messages'] = []; }
        foreach($errors as $error){ array_push($_SESSION['messages'], ['type' => 'error', 'text' => $error]); }
        return count($errors) === 0;
    }


    public function __toString()
    {
        $res = '';
        foreach($this->fields as $field){
            $res .= strval($field);
        }
        return $res;
    }


    protected function attrs($fieldname, $attrs)
    {
        foreach($attrs as $attr){ if(!is_string($attr)){ throw new Exception("All fields must be a string"); } }
        if(!isset($this->fields[$fieldname])){ throw new Exception("Key is invalid"); }
        $arr = [];
        foreach($attrs as $key => $val){ array_push($arr, "$key='$val'"); }
        $this->fields[$fieldname]->add_attr(implode(' ', $arr));
    }
}


?>