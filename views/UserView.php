<?php
require_once __dir__.'/../engine/view.php';
require_once __dir__.'/../models/User.php';
require_once __dir__.'/../models/Order.php';
require_once __dir__.'/../models/Message.php';

class UserView extends View
{

    public function __construct()
    {
        parent::__construct();
        $this->user = $this->request->session('import_auth');
        if($this->user) $this->user->role->id = @intval(array_shift(array_values((array)($this->user->role))));
        if(!$this->user){
            return $this->redirect('/');
        }
    }


    public function index()
    {
        $app_user = User::get_object_or_404($this->user->id);
        $unreaded_messages = Message::filter([ ['user_id', '=', $app_user->id] ]);

        if($this->request->method === 'POST')
        {
            $messages_on = $this->request->post('messages_on');
            if(!$messages_on)
            {
                //wyłączenie powiadomień mailowych
                $app_user->messages_allow = false;
                $app_user->save();
            }
        }

        $orders = [];
        switch($this->user->role->id)
        {
            case 1: $orders = Order::filter([ ['user_id', '=', $app_user->id] ]); break;
            case 2: $orders = Order::all(); break;
            case 5: $orders = Order::all(); break;
            case 2: $orders = Order::sql("SELECT id FROM orders WHERE user_id=$app_user->id OR last_user_id=$app_user->id"); break;
            case 6: $orders = Order::sql("SELECT o.id FROM orders o LEFT JOIN comments c ON o.id = c.order_id WHERE c.order_history_id=3"); break;
            case 4: $orders = Order::sql("SELECT o.id FROM orders o LEFT JOIN comments c ON o.id = c.order_id WHERE c.order_history_id=4"); break;
        }

        $orders_num = count($orders);

        return $this->render('account.index', [
            'user' => $this->user,
            'app_user' => $app_user,
            'orders_num' => $orders_num,
            'unreaded_messages' => $unreaded_messages
        ]);
    }

}

?>