<?php

trait ExternalDatabase
{
    public function external_query($query)
    {
        $result = [];
        global $database;
        global $external_db;
        $conn = new mysqli($database['host'], $database['user'], $database['password'], $external_db);
        if(!$conn){
            echo "Nie udało się połączyć z bazą danych";
            exit();
        }
	$conn->query("set names 'utf8'");
        $res = $conn->query($query);
        if($res && $res->num_rows){
            while($row = $res->fetch_assoc())
            array_push($result, $row);
        }
        return $result;
    }
}

?>