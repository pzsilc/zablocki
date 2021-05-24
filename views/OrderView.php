<?php
require_once __dir__.'/../engine/view.php';
require_once __dir__.'/../models/Order.php';
require_once __dir__.'/../models/File.php';
require_once __dir__.'/../models/OrderStatus.php';
require_once __dir__.'/../models/Priority.php';
require_once __dir__.'/../models/Stage.php';


class OrderView extends View
{
    const ORDERS_NUM = 30;

    public function __construct()
    {
        parent::__construct();
        $this->user = $this->request->session('import_auth');
        if(!$this->user){
            return $this->redirect('/login');
        }
        if($this->user) $this->user->role->id = @intval(array_shift(array_values((array)($this->user->role))));
    }



    private function get_ip()
    {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP'])) $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR'])) $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED'])) $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_X_CLUSTER_CLIENT_IP'])) $ipaddress = $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR'])) $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED'])) $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR'])) $ipaddress = $_SERVER['REMOTE_ADDR'];
        else $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }



    public function order_list()
    {
        if(in_array($this->user->role->id, [3, 4, 6, 2])) //widok tylko dla admina i zwykłego usera
        {
            return $this->redirect('/');
        }
        
        $filters = [];
        $filters['message'] = $this->request->get('message', '');
        $filters['status_id'] = $this->request->get('status_id', '');
        $filters['priority_id'] = $this->request->get('priority_id', '');
        $filters['stage_id'] = $this->request->get('stage_id', '');
	$filters['number'] = $this->request->get('number', '');

        $conditions = [];
        if($filters['status_id']) array_push($conditions, "status_id='".$filters['status_id']."'");
        if($filters['priority_id']) array_push($conditions, "priority_id='".$filters['priority_id']."'");
        if($filters['stage_id']) array_push($conditions, "stage_id='".$filters['stage_id']."'");
        if($filters['message']) array_push($conditions, "message LIKE '%".$filters['message']."%'");
	if($filters['number']) array_push($conditions, "id LIKE '%".$filters['number']."%'");
	
        $user_id = $this->user->id;
        $conditions = implode(' AND ', $conditions);
        $for_managements = $this->user->role->id === 4 ? 'OR status_id IN (5, 6)' : ''; //pobiera zam. oczekujące na akceptacje w przypadku użytkownika z zarządu
	$orders_num_on_page = self::ORDERS_NUM;
	$page = intval($this->request->get('page', '1'));
        $query = "
	    SELECT * 
	    FROM orders 
	    WHERE (user_id=$user_id OR last_user_id=$user_id $for_managements)
	    ".($conditions ? " AND $conditions" : '')." 
	    LIMIT $orders_num_on_page OFFSET ".(($page - 1) * $orders_num_on_page)."
	";
        $orders = Order::sql($query);
	$total_num = Order::sql("select count(*) as num from orders where (user_id=$user_id OR last_user_id=$user_id $for_managements) ".($conditions ? " AND $conditions" : ''))[0]->num;
	$filter_keys = array_keys($filters);
	$parameters = array_reduce($filter_keys, function($a, $e) use ($filters){
		$a .= '&'.$e.'='.$filters[$e];
		return $a;
	});

        foreach($orders as $order){ 
            $order->assign_relations();
            $order->message = $order->polish_convert_body();
        }
        return $this->render('orders.order_list', [
            'orders' => $orders,
            'filters' => $filters,
            'statuses' => OrderStatus::all(),
            'priorities' => Priority::all(),
            'stages' => Stage::all(),
            'user' => $this->user,
	    'total_num' => $total_num,
	    'page' => $page,
	    'total_num_of_pages' => ceil($total_num / $orders_num_on_page),
	    'parameters' => $parameters
        ]);
    }



    public function post()
    {
        $data = $this->request->post;
        $files = $this->request->files;

        if(!$data['_description'])
        {
            $this->add_message('error', 'Opis jest wymagany');
            $this->redirect('/');
        }
        $order = new Order();
        $order->ip = $this->get_ip();
        $order->user_id = $this->user->id;
        $order->message = $data['_description'];
        $order->created_at = date('Y-m-d H:i:s');
        $order->status_id = 1;
        $order->priority_id = 1;
        $order->save();

        if(isset($files['files']) && $files['files']['name'][0])
        {
            $_files = $files['files'];
            $len = count($_files['name']);
            for($i = 0; $i < $len; $i++)
            {
                $sample = Array('name' => $_files['name'][$i], 'tmp_name' => $_files['tmp_name'][$i]);
                File::create_new($sample, $order->id);
            }
        }

        $this->add_message("success", "Twoje zamówienie zostało dodane (nr id: $order->id)");
        return $this->redirect('/');
    }



    public function single($display_forms = true)
    {
        if(in_array($this->user->role->id, [3, 4, 6])) //widok tylko dla admina i zwykłego usera
        {
            return $this->redirect('/');
        }

        //walidacja
        $id = $this->request->get('id');
        if(!$id){
            return $this->redirect('/');
        }
        $order = Order::get_object_or_404($id);
        //zamiana relacji na atrybuty
        $order->assign_relations();
	$order->comments = Comment::sql("select * from comments where order_id=$order->id order by created_at, (case when order_history_id in (6, 8, 9, 17) then 2 else 1 end)");
	foreach($order->comments as $comm){
	    $comm->files = $comm->get_files();
	    $comm->user = $comm->get_user();
	    $comm->order_history = $comm->get_order_history();
	    $comm->content = $comm->polish_convert_body();
	}
        //pobiera wszystkie historie zamówienia (ich id)
        $history_ids = array_map(function($e){ 
            return $e->order_history_id; 
        }, $order->comments);
        //sprawdza czy zamówienie nie jest zakończone (wtedy nie wyświetla formularza komentarzy)
        if(in_array(9, $history_ids) || in_array(8, $history_ids) || in_array(6, $history_ids)){
            $display_forms = false;
        }

        $order->message = $order->polish_convert_body();
        
        //generowanie szablonu
        return $this->render('orders.single', [
            'order' => $order,
            'display_forms' => $display_forms
        ]);
    }
}

?>