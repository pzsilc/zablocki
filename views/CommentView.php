<?php
require_once __dir__.'/../engine/view.php';
require_once __dir__.'/../models/Comment.php';
require_once __dir__.'/../models/File.php';
require_once __dir__.'/../models/Message.php';
require_once __dir__.'/../models/Order.php';

class CommentView extends View
{
    public function __construct()
    {
        parent::__construct();
        $this->user = $this->request->session('import_auth');
    }

    public function post()
    {
        $data = $this->request->post;
        $files = $this->request->files;
        $order_id = $this->request->get('order_id');
        $order = Order::get_object_or_404($order_id);
        $from_admin_panel = boolval($this->request->get('admin'));

        $comment = new Comment();
        $comment->content = $data['content'];
        $comment->created_at = date('Y-m-d H:i:s');
        $comment->user_id = $this->user->id;
        $comment->order_id = $order_id;
        $comment->order_history_id = 0;
        $comment->save();

        if(isset($files['files']) && $files['files']['name'][0])
        {
            $_files = $files['files'];
            $len = count($_files['name']);
            for($i = 0; $i < $len; $i++)
            {
                $sample = Array('name' => $_files['name'][$i], 'tmp_name' => $_files['tmp_name'][$i]);
                File::create_new($sample, null, $comment->id);
            }
        }

        global $app_path;
        //dodanie wiadomości
        if($this->user->id == $order->user_id){
            Message::create_new($this->user, $order->get_last_user(), "<a href='$app_path/dashboard/orders/single?id=$order->id'>Link</a><br/>".$comment->content, $order->id);
        }
        elseif($this->user->id == $order->last_user_id){
            Message::create_new($this->user, $order->get_user(), "<a href='$app_path/dashboard/orders/single?id=$order->id'>Link</a><br/>".$comment->content, $order->id);
        }
        else{
            Message::create_new($this->user, $order->get_user(), "<a href='$app_path/dashboard/orders/single?id=$order->id'>Link</a><br/>".$comment->content, $order->id);
            Message::create_new($this->user, $order->get_last_user(), "<a href='$app_path/dashboard/orders/single?id=$order->id'>Link</a><br/>".$comment->content, $order->id);
        }

        $this->add_message('success', 'Dodano komentarz');
        $target = "/orders/single?id=$order_id";
        if($from_admin_panel) $target = '/dashboard'.$target;
        return $this->redirect($target);
    }

    public function delete()
    {
	//opcja tylko dla admina
	if($this->user->role->name !== 'Administrator' && $this->user->role->name !== 'Super Administrator')
	    return $this->redirect("/");
	$comment_id = $this->request->post("comment_id");
	$order_id = $this->request->post("order_id");
	$comment = Comment::get_object_or_404($comment_id);
	$comment->delete();
	$this->add_message('success', 'Usunieto komentarz');
	return $this->redirect("/dashboard/orders/single?id=$order_id");
    }
}

?>