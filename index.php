<?php

//ini_set('display_errors', 1);

require_once 'config.php';
$url = $_SERVER['REQUEST_URI'];
$url = explode('?', $url)[0];
$first_slash_pos = stripos($url, '/', 1);
$url = substr($url, $first_slash_pos);

require_once 'urls.php';
$match = false;
foreach($urls as $u){
    if ($u[0] === $url && $_SERVER['REQUEST_METHOD'] === $u[1][0]){
        $u[2]($u[1]);
        $match = true;
    }
}
if(!$match){
    require_once 'engine/view.php';
    $sample = new View();
    $sample->render('errors.404');
    http_response_code(404);
}

?>