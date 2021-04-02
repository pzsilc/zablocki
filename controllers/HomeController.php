<?php
require_once('engine/controller.php');

class HomeController extends Controller
{
    public function index()
    {
        return $this->render('index.php');
    }
}

?>