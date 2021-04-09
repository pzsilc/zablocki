<?php

function url($path, $view, $action, $http_method='GET')
{
    return [
        $path, 
        [$http_method, $view, $action], 
        function($data){ 
            $view = $data[1];
            $action = $data[2];
            require_once __dir__."/../views/$view.php";
            $obj = new $view();
            $obj->$action();
        }
    ];
}

?>
