<?php
require_once __dir__.'/../engine/view.php';

class HomeView extends View
{
    public function __construct()
    {
        parent::__construct();
        if(!$this->request->session('import_auth'))
            return $this->redirect('/login');
    }

    public function index()
    {
        $user = $this->request->session('import_auth');
        return $this->render('home', ['user' => $user]);
    }
}

?>