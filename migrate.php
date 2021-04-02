<?php
require_once('config.php');


final class Migrate
{
    private $table = null;
    private $attrs = [];

    public function __construct($class)
    {
        $this->table = $class::TABLE;
        $sample = new $class();
        $attrs = get_object_vars($sample);
        $this->attrs = $attrs;
    }

    public function run($conn)
    {
        $columns = $conn->query("SHOW COLUMNS FROM ".$this->table);
        $iterated_fields = [];
        while($row = $columns->fetch_assoc()){
            if(isset($this->attrs[$row['Field']])){

            } 
            else {
                $conn->query("ALTER TABLE $this->table DROP COLUMN ".$row['Field']);
            }
            array_push($iterated_fields, [$row['Field'] => null]);
        }

        foreach($this->attrs as $key => $value){
            if(!isset($iterated_fields[$key])){
                $conn->query("ALTER TABLE $this->table ADD $key ".$value->to_sql());
            }
        }
    }
}


global $database;
$conn = new mysqli($database['host'], $database['user'], $database['password'], $database['db']);
$conn->query("set charset utf8");
$models = scandir("models");
$models = array_slice($models, 2);
foreach($models as $model)
{
    require_once("models/$model");
    $class = explode('.', $model)[0];
    //migrate
    $migrate = new Migrate($class);
    $migrate->run($conn);
}

?>