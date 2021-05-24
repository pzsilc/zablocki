<?php
require_once __dir__.'/../config.php';


final class Migrator
{
    private $model = '';
    private $table = null;
    private $attrs = [];
    private $db_columns = [];
    public static $db_all_tables = [];

    public static function get_tables($conn)
    {
        $res = $conn->query("SHOW FULL TABLES");
        if($res->num_rows){
            while($row = $res->fetch_assoc()) {
                $r = null;
                foreach($row as $v){ $r = $v; break; }
                array_push(Migrator::$db_all_tables, $r);
            }
        }
    }

    public static function delete_those_exist($conn)
    {
        foreach(Migrator::$db_all_tables as $table){
            //delete old tables
            $conn->query("DROP TABLE $table");
            echo "Deleted $table table\r\n";
        }
    }

    private static function get_full_setting_for_column($attr)
    {
        $prop = $attr->name.' '.$attr->to_sql();
        if(!isset($attr->settings['required']) || $attr->settings['required'] == true) $prop .= ' NOT NULL';
        if(isset($attr->settings['unique']) && $attr->settings['unique'] == true) $prop .= ' UNIQUE';
        if(isset($attr->settings['default'])) $prop .= " DEFAULT '".$attr->settings['default']."'";
        return $prop;
    }

    public function __construct($md)
    {
        $model = explode('.', $md)[0];
        require_once "../models/$model.php";
        $this->model = $model;
        $this->table = $model::TABLE;
        $this->attrs = get_object_vars(new $model());
    }

    public function run($conn)
    {
        if(false !== $key = array_search($this->table, Migrator::$db_all_tables))
        {
            //update table
            $res = $conn->query("DESCRIBE $this->table");
            while($row = $res->fetch_assoc())
                array_push($this->db_columns, $row);
            unset(Migrator::$db_all_tables[$key]);

            $props = [];
            foreach($this->attrs as $attr){
                $match = false;
                $_s = '';
                $fs = self::get_full_setting_for_column($attr);
                foreach($this->db_columns as $key => $col){
                    if($col['Field'] === $attr->name){
                        $_s = 'MODIFY COLUMN '.$fs;
                        $match = true;
                        unset($this->db_columns[$key]);
                        array_push($props, $_s);
                    }
                }
                if(!$match){
                    $_s = 'ADD COLUMN '.$fs;
                    array_push($props, $_s);
                }
            }
            foreach($this->db_columns as $col){
                if($col['Field'] === 'id') continue;
                $_s = 'DROP COLUMN '.$col['Field'];
                array_push($props, $_s);
            }

            $sql = "ALTER TABLE $this->table ".implode(', ', $props);
            $conn->query($sql);
            echo "Updated $this->table table\r\n";
        }
        else 
        {
            //create new table
            $props = ['id INT NOT NULL PRIMARY KEY AUTO_INCREMENT'];
            foreach($this->attrs as $attr){
                $prop = self::get_full_setting_for_column($attr);
                array_push($props, $prop);
            }

            $sql = "CREATE TABLE $this->table (".implode(', ', $props).")";
            $conn->query($sql);
            echo "Created $this->table table\r\n";
        }
    }
}



$conn = new mysqli(
    $database['host'],
    $database['user'],
    $database['password'],
    $database['db']
);
$dir = scandir('../models');
Migrator::get_tables($conn);
foreach(array_slice($dir, 2) as $file)
{
    if($file === '.gitignore') continue;
    $migrator = new Migrator($file);
    $migrator->run($conn);
}
Migrator::delete_those_exist($conn);
$conn->close();

?>