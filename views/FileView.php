<?php
require_once __dir__.'/../engine/view.php';
require_once __dir__.'/../models/File.php';

class FileView extends View
{

    public function __construct()
    {
        parent::__construct();
        $user = $this->request->session('import_auth');
        if(!$user) return $this->redirect('/');
    }


    public function index()
    {
        $id = $this->request->get('id');
        if(!$id) 
            return $this->redirect('/');
        
        $file = File::get_object_or_404($id);
        $path = File::PUBLIC_PATH.'/'.$file->name;

        if(!file_exists($path))
            die('File not found');
        else{
            header("Cache-Control: public");
            header("Content-Description: File Transfer");
            header("Content-Disposition: attachment; filename=$path");
            header("Content-Type: application/zip");
            header("Content-Transfer-Encoding: binary");
            // read the file from disk
            readfile($path);
        }
    }
}

?>