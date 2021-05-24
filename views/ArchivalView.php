<?php
require_once __dir__.'/../engine/view.php';
require_once __dir__.'/../models/Order.php';
require_once __dir__.'/../models/OrderStatus.php';
require_once __dir__.'/../models/User.php';
require_once __dir__.'/../models/Priority.php';

class ArchivalView extends View
{
    const FETCH_ORDERS_NUM = 30;


    public function __construct()
    {
        parent::__construct();
        $this->user = $this->request->session('import_auth');
        if($this->user) $this->user->role->id = @intval(array_shift(array_values((array)($this->user->role))));
        if(!$this->user || $this->user->role->id == 1){
            return $this->redirect('/');
        }
    }



    public function index()
    {
        $filters = [];
        $conditions = [];
        $constrait = ''; //pola które precyzują do których pól zawęzić wyszukiwanie dla wykonawcy oraz zarządcy
        //ograniczenie zawartości dla poszczególnych userów
        if($this->user->role->id !== 2 && $this->user->role->id !== 5){ //admin widzi wszystkie zamówienia, reszta tylko powiązane ze sobą
            $constrait = '(user_id='.$this->user->id.' OR last_user_id='.$this->user->id.')';
        }

        
        //filtry
        $filters['message'] = $this->request->get('message', '');
	    $filters['number'] = $this->request->get('number', '');
        $filters['priority_id'] = $this->request->get('priority_id', '');
        $filters['status_id'] = $this->request->get('status_id', '');
        if(!in_array($filters['status_id'], [3, 8, 9])) $filters['status_id'] = '';

        //zamiana filtrów do stringa
        if($filters['priority_id']) array_push($conditions, ['priority_id', '=', $filters['priority_id']]);
        if($filters['status_id']) array_push($conditions, ['status_id', '=', $filters['status_id']]);
        if($filters['message']) array_push($conditions, ['message', 'LIKE', '%'.$filters['message'].'%']);
	    if($filters['number']) array_push($conditions, ['id', 'LIKE', '%'.$filters['number'].'%']);

        //parametry
        $orders = Order::filter($conditions, ['created_at', 'DESC']);
        $conditions = array_map(function($e){ return " ".$e[0]." ".$e[1]." '".$e[2]."' "; }, $conditions);
        $parameters = implode('&', $conditions);
        $parameters = str_replace(' ', '', $parameters);
        $parameters = str_replace("'", '', $parameters);
        $conditions = implode(' AND ', $conditions);


        //obsługa paginatora
        $page = $this->request->get('page', 1);
        if($page == '') $page = 1;
        $num_on_page = self::FETCH_ORDERS_NUM;

        if($this->user->role->id !== 6){
            //główne zapytanie
            $sql = "SELECT * 
                FROM orders 
                WHERE status_id IN (3, 8, 9) 
                ".($conditions ? " AND $conditions " : '')."
                ".($constrait ? ($conditions ? " AND $constrait " : " AND $constrait ") : "")."
                LIMIT $num_on_page OFFSET ".($num_on_page * ($page - 1))."
            ";
            //pobiera liczbę wybranych zamówień
            $total_num_of_orders = Order::sql("
                SELECT COUNT(*) AS num 
                FROM orders AS o
                WHERE o.status_id IN (3,8,9) ".($conditions ? " AND $conditions " : '')."
                ".($constrait ? ($conditions ? " AND $constrait " : " AND $constrait ") : "")."
            ")[0]->num; 
        }
        else {
            //główne zapytanie
            $sql = "
                SELECT o.id, o.message, o.created_at, o.status_id, o.priority_id
                FROM orders o LEFT JOIN comments c ON c.order_id=o.id
                WHERE c.order_history_id=3 AND o.status_id IN (3,8,9) ".($conditions ? " AND $conditions" : '')."
                GROUP BY o.id
                LIMIT $num_on_page OFFSET ".($num_on_page * ($page - 1))."
            ";
            //pobiera liczbe wybranych zamówień
            $total_num_of_orders = Order::sql("
                SELECT count(*) as num
                FROM orders o LEFT JOIN comments c ON c.order_id=o.id
                WHERE c.order_history_id=3 AND o.status_id IN (3,8,9) ".($conditions ? " AND $conditions" : '')."
                GROUP BY o.id
                LIMIT $num_on_page OFFSET ".($num_on_page * ($page - 1))."
            ")[0]->num;
        }
        $orders = Order::sql($sql);


        //obliczanie ilości stron
        $total_num_of_pages = ceil($total_num_of_orders / $num_on_page);
        //przypisanie relacji do zamówień
        foreach($orders as $order){
            $order->assign_relations(false);
            $order->message = $order->polish_convert_body();
        }

        //użytkownicy
        $users = User::filter([ ['role_id', '=', 3] ]);
        foreach($users as $user) 
            $user->external_user = $user->get_external_user();
        //generowanie htmla
        return $this->render('dashboard.archival', [
            'orders' => $orders,
            'user_role_id' => $this->user->role->id,
            'statuses' => OrderStatus::sql("SELECT * FROM order_statuses WHERE id=3 OR id=8 OR id=9"), //pobiera tylko statusy skończone
            'priorities' => Priority::all(),
            'filters' => $filters,
            'total_num_of_orders' => $total_num_of_orders,
            'total_num_of_pages' => $total_num_of_pages,
            'page' => $page,
            'parameters' => $parameters
        ]);
    }
}

?>