<?php


abstract class Field
{
    public $name;
    public $settings;
    public $attrs;

    protected function __construct($name, $settings){
        $this->name = $name;
        $this->settings = $settings;
        if(!isset($settings['required']) || $settings['required'] == true){ $this->attrs .= ' required '; }
    }

    public static function init($name, $settings=[]){
        $class = get_called_class();
        if(!is_array($settings)){ throw new Exception('Invalid args'); }
        if(method_exists($class, 'valid')){ $class::valid($settings); }
        return new $class($name, $settings);
    }

    public function add_attr($attr)
    {
        $this->attrs .= ' '.$attr.' ';
    }

    public abstract function to_sql();

    public abstract function __toString();
}




class CharField extends Field
{
    public static function valid($settings){
        if(!isset($settings['max'])){ throw new Exception('Have to define max'); }
    }

    public function to_sql(){
        return "VARCHAR(".$this->settings['max'].")";
    }

    public function __toString(){
        return "<input type='text' name='$this->name' $this->attrs/>";
    }
}

class TextField extends Field
{
    public static function valid($settings){
        if(!isset($settings['max'])){ throw new Exception('Have to define max'); }
    }

    public function to_sql(){
        return "VARCHAR(".$this->settings['max'].")";
    }

    public function __toString(){
        return "<textarea name='$this->name' $this->attrs></textarea>";
    }
}

class IntegerField extends Field
{
    public function to_sql(){
        return "INT";
    }

    public function __toString(){
        return "<input type='number' name='$this->name' $this->attrs/>";
    }
}

class DecimalField extends Field
{
    public static function valid($settings){
        if(!isset($settings['numbers_qty'])){ throw new Exception('Have to define numbers quantity'); }
        if(!isset($settings['precision'])){ throw new Exception('Have to define precision'); }
        if($settings['precisions'] < $settings['numbers_qty']){ throw new Exception('Precision must be greater than numbers quantity'); }
    }
    
    public function to_sql(){
        return "DECIMAL(".$this->settings['precision'].' '.$this->settings['numbers_qty'].")";
    }

    public function __toString(){
        return "<input type='number' step='".$this->settings['step']."' name='$this->name' $this->attrs/>";
    }
}

class BooleanField extends Field
{
    public function to_sql(){
        return "BOOLEAN";
    }

    public function __toString(){
        return "<input type='checkbox' name='$this->name' $this->attrs/>";
    }
}

class EmailField extends Field
{
    public function to_sql(){
        return "VARCHAR(".$this->settings['max'].")";
    }

    public function __toString(){
        return "<input type='email' name='$this->name' $this->attrs/>";
    }
}

class PasswordField extends Field
{
    public static function valid($settings){
        if(!isset($settings['max'])){ throw new Exception('Have to define max'); }
    }

    public function to_sql(){
        return "VARCHAR(".$this->settings['max'].")";
    }

    public function __toString(){
        return "<input type='password' name='$this->name' $this->attrs/>";
    }
}

class DateTimeField extends Field
{
    public function to_sql(){
        return "DATETIME";
    }

    public function __toString(){
        return "<input type='datetime-local' name='$this->name' $this->attrs/>";
    }
}







?>