<?php
require_once __dir__.'/../config.php';


abstract class Model
{
    const TABLE = '';
    private $id = null;

    

    public function __get($name)
    {
        if($name == 'id'){
            return $this->id;
        }
    }

    public function __toString()
    {
        return strval($this->id);
    }

    protected static function qexec($query)
    {
        global $database;
        $conn = new mysqli($database['host'], $database['user'], $database['password'], $database['db']);
	    $conn->query("set names 'utf8'");
        $results = $conn->query($query);
        if($conn->insert_id){ return $conn->insert_id; }
        if(!is_object($results) || $results->num_rows == 0) return [];
        $arr = [];
        $class = get_called_class();
        while($row = $results->fetch_assoc()){
            $obj = new $class();
            foreach($row as $key=>$val){
                $obj->$key = $val;
            }
            array_push($arr, $obj);
        }
        return $arr;
    }

    public static function all()
    {
        $class = get_called_class();
        return $class::qexec("SELECT * FROM ".$class::TABLE);
    }

    public static function get_object_or_404($id)
    {
        $class = get_called_class();
        $result = $class::qexec("SELECT * FROM ".$class::TABLE." WHERE id=$id");
        if(!$result){
            http_response_code(404);
            require_once 'view.php';
            $view = new View();
            $view->render('errors.404');
            die();
        }
        return $result[0];
    }

    public static function get($id)
    {
        $class = get_called_class();
        $res = $class::filter([ ['id', '=', $id] ]);
        return $res ? $res[0] : null;
    }

    public static function filter($arr, $sort = null)
    {
        $class = get_called_class();
        $_arr = [];
        foreach($arr as $elem) { 
            if(count($elem) != 3) throw new Exception('Invalid input');
            array_push($_arr, $elem[0]." ".$elem[1]." '".$elem[2]."'");
        }
        $cond = implode(' AND ', $_arr);
        if($sort && count($sort) !== 2) throw new Exception("Invalid length of sort array");
        $query = "SELECT * FROM ".$class::TABLE.($cond ? " WHERE ".$cond : '').($sort ? " ORDER BY ".$sort[0]." ".$sort[1] : '');
        return $class::qexec($query);
    }

    public static function sql($query)
    {
        $class = get_called_class();
        return $class::qexec($query);
    }

    public static function max($col_name)
    {
        $class = get_called_class();
        return $class::qexec("SELECT MAX($col_name) as $col_name FROM ".$class::TABLE)[0]->$col_name;
    }

    public static function min($col_name)
    {
        $class = get_called_class();
        return $class::qexec("SELECT MIN($col_name) as $col_name FROM ".$class::TABLE)[0]->$col_name;
    }

    public static function count()
    {
        $class = get_called_class();
        return $class::qexec("SELECT COUNT(id) as _count FROM ".$class::TABLE)[0]->_count;
    }

    public function save($commit=true)
    {
        $class = get_called_class();
        if(!$this->id)
        {
            if($commit == false){
                $this->id = $class::max('id') + 1;
                return;
            }

            $query = "INSERT INTO ".$class::TABLE." (";
            $f = true;
            foreach(get_object_vars($this) as $key=>$val){
                if($key == 'id') { continue; }
                if($f) { $query .= $key; $f = false; }
                else { $query .= ', '.$key; }
            }
            $query .= ') VALUES (';
            $f = true;
            foreach(get_object_vars($this) as $key=>$val){
                if($key == 'id') { continue; }
                if($f) { $query .= '"'.$val.'"'; $f = false; }
                else { $query .= ', '.'"'.$val.'"'; }
            }
            $query .= ')';
            $this->id = $class::qexec($query);
            return;
        }
        else
        {
            $query = "UPDATE ".$class::TABLE." SET";
            $f = true;
            foreach(get_object_vars($this) as $key=>$val){
                if($f) { $query .= ' '.$key.'="'.$val.'"'; $f = false; }
                else { $query .= ', '.$key.'="'.$val.'"'; }
            }
            $query .= " WHERE id=$this->id";
            return $class::qexec($query);
        }
    }

    public function delete()
    {
        if($this->id)
        {
            $class = get_called_class();
            $query = "DELETE FROM ".$class::TABLE." WHERE id=$this->id";  
            return $class::qexec($query);
        }
        else throw new Exception("Cannot delete unsaved object");
    }
}

?>