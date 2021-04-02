<?php
require_once('engine/request.php');


abstract class Controller
{
    protected $request;


    public function __construct()
    {
        $this->request = new Request();
        if($this->request->method == 'POST'){
            if(!$this->request->post('csrf_token') || $this->request->post('csrf_token') != $this->request->session('csrf_token')){
                http_response_code(403);
                $this->render('errors/403');
                die();
            }
            $this->request->unset_session('csrf_token');
        }
    }

    protected function add_message($type, $text)
    {
        if(!isset($_SESSION['messages'])){ $_SESSION['messages'] = []; }
        array_push($_SESSION['messages'], ['type' => $type, 'text' => $text]);
    }

    private function generate_csrf()
    {
        $token = md5(uniqid());
        $this->request->set_session('csrf_token', $token);
        return "<input type='hidden' name='csrf_token' value='".$this->request->session('csrf_token')."'/>";
    }

    protected function redirect($url)
    {
        global $app_path;
        header("Location: $app_path$url");
        exit();
    }

    protected function render($dir, $args=[])
    {
        extract($args);
        $csrf_token = $this->generate_csrf();
        global $app_name;
        global $app_path;
        $messages = null;
        if(isset($_SESSION['messages'])){
            $messages = $_SESSION['messages'];
            unset($_SESSION['messages']);
        }
        include("statics/templates/$dir.php");
    }
}

?>