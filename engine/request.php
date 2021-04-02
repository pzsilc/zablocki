<?php
session_start();


class Request 
{
    private $method;
    private $post;
    private $get;
    private $session;
    private $server;

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->post = array_map(function($e){ return htmlentities($e); }, $_POST);
        $this->get = array_map(function($e){ return htmlentities($e); }, $_GET);
        $this->session = $_SESSION;
        $this->server = $_SERVER;
    }

    public function __get($name)
    {
        return $this->$name;
    }

    public function get($key = null, $default_val = null)
    {
        if(!$key) { return $this->get; }
        return (isset($this->get[$key]) ? $this->get[$key] : $default_val);
    }

    public function post($key = null, $default_val = null)
    {
        if(!$key) { return $this->post; }
        return (isset($this->post[$key]) ? $this->post[$key] : $default_val);
    }

    public function session($key = null, $default_val = null)
    {
        if(!$key) { return $this->session; }
        return (isset($this->session[$key]) ? $this->session[$key] : $default_val);
    }

    public function set_session($key, $value)
    {
        $_SESSION[$key] = $value;
        $this->session[$key] = $value;
    }

    public function unset_session($name)
    {
        if(isset($this->session[$name]))
        {
            unset($_SESSION[$name]);
            unset($this->session[$name]);
        }
    }
}

?>