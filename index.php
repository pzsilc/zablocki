<?php

require_once('urls.php');
require_once('config.php');
$url = $_SERVER['REQUEST_URI'];
$url = explode('?', $url)[0];
$first_slash_pos = stripos($url, '/', 1);
$url = substr($url, $first_slash_pos);

if(!isset($routes[$url])){
    http_response_code(404);
    global $app_path;
    require_once __dir__.'/engine/controller.php';
    $c = new Controller();
    $c->render('errors.404');
    die();
}

$resource = $routes[$url];
$resource = explode('|', $resource);
require_once('controllers/'.$resource[0].'.php');
$class = explode('.', $resource[0])[0];
$class = ucfirst($class);
$controller = new $class();
$resource = $resource[1];
$controller->$resource();

?>