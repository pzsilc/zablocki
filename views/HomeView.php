<?php
require_once __dir__.'/../engine/view.php';

class HomeView extends View
{
    public function index()
    {
        return $this->render('index', [
            'p' => 10
        ]);
    }
}

?>