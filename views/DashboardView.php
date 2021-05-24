<?php
require_once __dir__.'/../engine/view.php';
require_once __dir__.'/../models/Order.php';
require_once __dir__.'/../models/Priority.php';
require_once __dir__.'/../models/Comment.php';
require_once __dir__.'/../models/File.php';
require_once __dir__.'/../models/Stage.php';
require_once __dir__.'/../models/OrderHistory.php';
require_once __dir__.'/../models/Origin.php';
require_once __dir__.'/../views/MailView.php';



class DashboardView extends View
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
        $conditions = [ ['o.status_id', '!=', 3], ['o.status_id', '!=', 8], ['o.status_id', '!=', 9] ];
        $constrait = ''; //pola które precyzują do których pól zawęzić wyszukiwanie dla wykonawcy oraz zarządcy
        //ograniczenie zawartości dla poszczególnych userów
        if($this->user->role->id === 3){ //wykonawca
            $constrait = '(o.user_id='.$this->user->id.' OR o.last_user_id='.$this->user->id.')';
        }
        elseif($this->user->role->id === 6){ //wykonawca 2 (ustala terminny płatności){
            $constrait = 'o.status_id=10';
        }
        elseif($this->user->role->id === 4){ //zarząd
            $constrait = '(o.status_id=5 OR o.status_id=6)';
        }
        
        //filtry
        $filters['user_id'] = $this->request->get('user_id', '');
        $filters['last_user_id'] = $this->request->get('last_user_id', '');
	$filters['number'] = $this->request->get('number', '');
        $filters['message'] = $this->request->get('message', '');
        if(!in_array($this->user->role->id, [6, 4])) //nie można filtrować dla tych ról ponieważ ich listy wyświetlają się wg ich konkretnych statusów
            $filters['status_id'] = $this->request->get('status_id', '');
        $filters['priority_id'] = $this->request->get('priority_id', '');
        $filters['stage_id'] = $this->request->get('stage_id', '');

        //zamiana filtrów do stringa
        if($filters['user_id']) array_push($conditions, ['user_id', '=', $filters['user_id']]);
        if($filters['last_user_id']) array_push($conditions, ['last_user_id', '=', $filters['last_user_id']]);
        if(!in_array($this->user->role->id, [6, 4])) //nie można filtrować dla tych ról ponieważ ich listy wyświetlają się wg ich konkretnych statusów
            if($filters['status_id']) array_push($conditions, ['status_id', '=', $filters['status_id']]);
        if($filters['priority_id']) array_push($conditions, ['priority_id', '=', $filters['priority_id']]);
        if($filters['stage_id']) array_push($conditions, ['stage_id', '=', $filters['stage_id']]);
        if($filters['message']) array_push($conditions, ['message', 'LIKE', '%'.$filters['message'].'%']);
	if($filters['number']) array_push($conditions, ['id', 'LIKE', '%'.$filters['number'].'%']);

        $orders = Order::filter($conditions, ['created_at', 'DESC']);
        $conditions = array_map(function($e){ return " ".$e[0]." ".$e[1]." '".$e[2]."' "; }, $conditions);
        $parameters = implode('&', $conditions);
        $parameters = str_replace(' ', '', $parameters);
        $parameters = str_replace("'", '', $parameters);
        $conditions = implode(' AND ', $conditions);

        $page = $this->request->get('page', 1);
        if($page == '') $page = 1;
        $num_on_page = self::FETCH_ORDERS_NUM;

        $orders = Order::sql("
            select 
                o.id, 
                o.created_at,
                o.status_id,
                o.stage_id,
                o.message,
                o.user_id,
                o.last_user_id,
                o.priority_id,
                c.content as scheduled_date 
            from orders o 
            left join( 
                select 
                    content, 
                    created_at,
                    order_id,
                    order_history_id
                from comments
            ) c on o.id=c.order_id and c.order_history_id=(
                case
                when o.stage_id=1 then 11
                when o.stage_id=2 then 12
                when o.stage_id=3 then 13
                when o.stage_id=4 then 14
                else 7 
                end
            )     
            ".($conditions ? " WHERE $conditions " : '')."
            ".($constrait ? ($conditions ? " AND $constrait " : " WHERE $constrait ") : "")."      
            order by 
                (case when o.status_id in (3, 8, 9) then 2 else 1 end),
                (case when scheduled_date is null then 2 else 1 end),
                str_to_date(scheduled_date, '%Y-%m-%d') ASC
            LIMIT $num_on_page OFFSET ".($num_on_page * ($page - 1))."
        ");

        //pobiera liczbę wybranych zamówień
        $total_num_of_orders = Order::sql("
            SELECT COUNT(*) AS num 
            FROM orders AS o
            ".($conditions ? " WHERE $conditions " : '')."
            ".($constrait ? ($conditions ? " AND $constrait " : " WHERE $constrait ") : "")."
        ")[0]->num; 
        $total_num_of_pages = ceil($total_num_of_orders / $num_on_page);

        //przypisanie relacji do zamówień
        foreach($orders as $order){
            $order->assign_relations(false);
            $order->message = $order->polish_convert_body();
        }

        //użytkownicy
        $users = User::all();
        $executors = User::sql("SELECT * FROM users WHERE role_id IN (3,6,2,5)");
        foreach($users as $user) 
            $user->external_user = $user->get_external_user();
        foreach($executors as $executor)
            $executor->external_user = $executor->get_external_user();
        //generowanie htmla
        return $this->render('dashboard.index', [
            'orders' => $orders,
            'user_role_id' => $this->user->role->id,
            'users' => $users,
            'executors' => $executors,
            'statuses' => OrderStatus::filter([ ['id', '!=', 3], ['id', '!=', 8], ['id', '!=', 9] ]),
            'priorities' => Priority::all(),
            'stages' => Stage::all(),
            'filters' => $filters,
            'total_num_of_orders' => $total_num_of_orders,
            'total_num_of_pages' => $total_num_of_pages,
            'page' => $page,
            'parameters' => $parameters
        ]);
    }



    public function single()
    {
        $display_controls_form = true;
        $display_comment_form = true;
        $display_management_request_form = true;
        $display_paytime_settings = false;
        $display_paytime_order = true;
        $message = '';

        $id = $this->request->get('id');
        $order = Order::get_object_or_404($id);
        $priorities = Priority::all();
        $executors = User::sql("SELECT * FROM users WHERE role_id IN (2, 3, 5)");

        //konwersja relacji
        $order->assign_relations();
        $order->comments = Comment::sql("select * from comments where order_id=$order->id order by created_at, (case when order_history_id in (6, 8, 9, 17) then 2 else 1 end)");
        foreach($order->comments as $comm){
            $comm->files = $comm->get_files();
            $comm->user = $comm->get_user();
            $comm->order_history = $comm->get_order_history();
            $comm->content = $comm->polish_convert_body();
        }
        $order->message = $order->polish_convert_body();
        foreach($executors as $executor){
            $executor->external_user = $executor->get_external_user();
        }

        //przefiltrowanie zawartości szablonu w zależności od etapu zamówienia
        $not_display_form_for_statuses = [3, 5, 6, 8, 9];
        if(in_array($order->status_id, $not_display_form_for_statuses))
        {
            $display_controls_form = false;
            $display_comment_form = false;
            if(in_array($order->status_id, [5,6])){
                $message = 'Oczekiwanie na zgodę zarządu';
            }
        }

        //zlecony termin płatności (wyświetlanie innej zawartości po komentarzach)
        $order_history_ids = array_map(function($comment){ 
            return intval($comment->order_history_id); 
        }, $order->comments);
        if(in_array(3, $order_history_ids) && !in_array(17, $order_history_ids)){
            $display_paytime_settings = true;
        }

        //decyzja o wyświetlaniu formularza zarządowego
        foreach($order->comments as $comment){
            if($comment->order_history_id == 10){
                $display_management_request_form = false;
            }
        }

        //decyzja o wyświetlaniu formularza terminu płatności
        foreach($order->comments as $comment){
            if($comment->order_history_id == 17){
                $display_paytime_order = false;
            }
        }

        //zwrócenie
        return $this->render('dashboard.single', [
            'order' => $order,
            'priorities' => $priorities,
            'executors' => $executors,
            'display_controls_form' => $display_controls_form,
            'display_comment_form' => $display_comment_form,
            'display_management_request_form' => $display_management_request_form,
            'display_paytime_settings' => $display_paytime_settings,
            'display_paytime_order' => $display_paytime_order,
            'message' => $message,
            'user' => $this->user,
            'origins' => Origin::all()
        ]);
    }



    public function proceed()
    {
        $now = date('Y-m-d H:i:s');
        $order_id = $this->request->get('id');
        $data = $this->request->post;
        $order = Order::get_object_or_404($order_id);
        //aktualizacja rozpoczęcia zlecenia
        if(!$order->get_started_datetime())
        {
            $order->set_start_date($now, $this->user->id);
            $order->status_id = 2;
            $order->save();
        }
        //aktualizacja priorytetu
        if(isset($data['priority_id']) && $data['priority_id'] !== $order->priority_id)
        {
            $order->priority_id = $data['priority_id'];
            $order->save();
            $this->add_message('success', "Zaktualizowano priorytet");
        }
        //aktualizacja planowanej daty realizacji zlecenia
        if(isset($data['execution-date']) && $data['execution-date'] !== '')
        {
            if($order->get_scheduled_datetime()){
                $comments = Comment::filter([ ['order_id', '=', $order->id], ['order_history_id', '=', 7] ]);
                foreach($comments as $comm){
                    $comm->order_history = $order->get_order_history();
                    $comm->content = $comm->order_history->name.' na '.$comm->content;
                    $comm->order_history_id = 0;
                    unset($comm->order_history);
                    $comm->save();
                }
            }
            $order->set_schedule_date($data['execution-date'], $this->user->id);
            $order->save();
            $this->add_message('success', "Zaplanowano datę realizacji");
        }
        //odrzucono zlecenie
        if($this->request->post('reject-order') === 'on')
        {
            $order->status_id = 8;
            $order->save();
            $comment = new Comment();
            $comment->content = '';
            $comment->created_at = $now;
            $comment->user_id = $this->user->id;
            $comment->order_id = $order->id;
            $comment->order_history_id = 8;
            $comment->save();
            $this->add_message('success', "Odrzucono zlecenie");
        }
        //aktualizacja etapu
        if((isset($data['next-stage']) || isset($data['skip-to-logistic'])) && ($data['next-stage'] === 'on' || $data['skip-to-logistic'] === 'on'))
        {
            if($data['skip-to-logistic'] === 'on') $order->stage_id = 2;
            else $order->stage_id++;
            $order->save();
        }
        //aktualizacja źródła
        if(isset($data['origin-id']) && $data['origin-id'] !== $order->origin_id)
        {
            $order->origin_id = $data['origin-id'];
            $order->save();
        }
        //uznano za niestosowne
        if($this->request->post('inappropriate-order') === 'on')
        {
            $order->status_id = 9;
            $order->save();
            $comment = new Comment();
            $comment->content = '';
            $comment->created_at = $now;
            $comment->user_id = $this->user->id;
            $comment->order_id = $order->id;
            $comment->order_history_id = 9;
            $comment->save();
            $this->add_message('success', "Uznano za niestosowne");
        }
        //zakończono zlecenie
        if($this->request->post('close-order') === 'on')
        {
            $order->status_id = 3;
            $order->set_end_date($now, $this->user->id);
            $order->save();
            $this->add_message('success', "Zakończono zlecenie");
        }
        //wysłanie maila
        if($this->request->post('close-order') === 'on' || $this->request->post('inappropriate-order') === 'on' || $this->request->post('reject-order') === 'on')
        {
            MailView::send(
                'Zamknieto zamowienie',
                'Twoje zlecenie o numerze #'.$order->id.' zostalo zamkniete.',
                $order->get_user(),
                '/dashboard/orders/single?id='.$order->id
            );
        }
        else return $this->redirect('/dashboard/orders/single?id='.$order->id);
    }



    public function stage_dates()
    {
        $now = date('Y-m-d H:i:s');
        $order_id = $this->request->get('id');
        $order = Order::get_object_or_404($order_id);
        $data = $this->request->post;
        if($data['production_date'] === $order->get_production_date()) 
            unset($data['production_date']);
        if($data['logistic_date'] === $order->get_logistic_date()) 
            unset($data['logistic_date']);
        if($data['transport_date'] === $order->get_transport_date()) 
            unset($data['transport_date']);
        if($data['complaint_date'] === $order->get_complaint_date()) 
            unset($data['complaint_date']);
        unset($data['csrf_token']);

        foreach($data as $key => $val)
        {
            //nazwy pól i odpowiadające im id w bazie (order_history_id)
            $history_labels = ['production_date' => 11, 'logistic_date' => 12, 'transport_date' => 13, 'complaint_date' => 14];
            $history_id = $history_labels[$key];

            $same_comments = Comment::filter([ ['order_history_id', '=', $history_id], ['order_id', '=', $order->id] ]);
            foreach($same_comments as $comm){
                $comm->order_history = $comm->get_order_history();
                $comm->content = $comm->order_history->name.' na '.$comm->content;
                $comm->order_history_id = 0;
                unset($comm->order_history);
                $comm->save();
            }

            $comment = new Comment();
            $comment->content = $val;
            $comment->created_at = $now;
            $comment->user_id = $this->user->id;
            $comment->order_id = $order->id;
            $comment->order_history_id = $history_id;
            $comment->save();
        }


        $this->add_message('success', "Ustawiono daty etapów");
        return $this->redirect('/dashboard/orders/single?id='.$order->id);
    }



    public function order_execution()
    {
        $now = date('Y-m-d H:i:s');
        $order_id = $this->request->get('id');
        $executor_id = $this->request->post('executor_id');
        $executor = User::get_object_or_404($executor_id);
        $executor->external_user = $executor->get_external_user();
        $order = Order::get_object_or_404($order_id);

        $order->last_user_id = $executor->id;
        $order->save();
        $comment = new Comment();
        $comment->content = $executor->external_user->first_name.' '.$executor->external_user->last_name;
        $comment->created_at = $now;
        $comment->user_id = $this->user->id;
        $comment->order_id = $order->id;
        $comment->order_history_id = 2;
        $comment->save();
        $first_name = $executor->external_user->first_name;
        $last_name = $executor->external_user->last_name;
        $this->add_message('success', "Zlecono zykonanie zlecenia dla $first_name $last_name");
        MailView::send(
            'Nowe zgloszenie',
            'Zlecono Tobie wykonanie zamowienia o numerze #'.$order->id,
            $executor,
            '/dashboard/orders/single?id='.$order->id
        );
    }



    public function order_transaction()
    {
        $order_id = $this->request->get('id');
        $order = Order::get_object_or_404($order_id);
        $order->status_id = 10;
        $order->save();
        $content = $this->request->post('content');
        $comment = new Comment();
        $comment->content = "Zlecono ustalenie daty płatności";
        if($content) $comment->content .= ' '.$content;
        $comment->created_at = date('Y-m-d H:i:s');
        $comment->user_id = $this->user->id;
        $comment->order_id = $order_id;
        $comment->order_history_id = 3;
        $comment->save();
        $this->add_message('success', "Zlecono ustalenie terminu zapłaty");
        $executors_2 = User::filter([ ['role_id', '=', 6] ]);
        foreach($executors_2 as $executor_2)
        {
            MailView::send(
                'Zlecono ustawienie terminu platności',
                'Zlecono ustalenie terminu platności dla zamowienia o numerze #'.$order->id,
                $executor_2,
                '/dashboard/orders/single?id='.$order->id
            );
        }
    }



    public function management_request()
    {
        $order_id = $this->request->get('id');
        $order = Order::get_object_or_404($order_id);
        if($order->status_id == 1) $order->status_id = 5;
        else $order->status_id = 6;
        $order->save();
        $comment = new Comment();
        $comment->content = "";
        $comment->created_at = date('Y-m-d H:i:s');
        $comment->user_id = $this->user->id;
        $comment->order_id = $order->id;
        $comment->order_history_id = 4;
        $comment->save();
        $this->add_message('success', "Wystąpiono o zgodę zarządu");
        $managers = User::filter([ ['role_id', '=', 4] ]);
        foreach($managers as $manager)
        {
            MailView::send(
                'Wystapiono o zgode zarzadu',
                'Zlecenie o numerze #'.$order->id.' wymaga akceptacji. Potwierdz lub odrzuc zlecenie',
                $manager,
                '/dashboard/orders/single?id='.$order->id
            );
        }
    }



    public function management_accept()
    {
        $order_id = $this->request->get('id');
        $order = Order::get_object_or_404($order_id);
        $if_accepted = $this->request->post('action') == 'Zaakceptuj';
        if($if_accepted)
        {
            if($order->status_id == 5) $order->status_id = 1;
            else $order->status_id = 2;
            $order->save();
            $comment = new Comment();
            $comment->content = "";
            $comment->created_at = date('Y-m-d H:i:s');
            $comment->user_id = $this->user->id;
            $comment->order_id = $order->id;
            $comment->order_history_id = 10;
            $comment->save();
        }
        else
        {
            $order->status_id = 8;
            $order->save();
            $comment = new Comment();
            $comment->content = "";
            $comment->created_at = date('Y-m-d H:i:s');
            $comment->user_id = $this->user->id;
            $comment->order_id = $order->id;
            $comment->order_history_id = 8;
            $comment->save();
        }

        $this->add_message('success', ($if_accepted ? 'Zaakceptowano' : 'Odrzucono').' zlecenie');
        if($order->last_user){
            MailView::send(
                'Zaakceptowano przez zarzad',
                'Zlecenie o numerze #'.$order->id.' zostalo zaakceptowane przez zarzad',
                $order->last_user,
                '/dashboard/orders/single?id='.$order->id
            );
        }
        else{
            $admins = User::filter([ ['role_id', '=', 2] ]);
            foreach($admins as $admin){
                MailView::send(
                    'Ustawiono termin platnosci',
                    'Ustawiono termin platnosci dla zlecenia #'.$order->id,
                    $admin,
                    '/dashboard/orders/single?id='.$order->id
                );
            }
        }
    }

    public function paytime_settings()
    {
        $now = date('Y-m-d H:i:s');
        $id = $this->request->get('id');
        $datetime = $this->request->post('paytime');
        $order = Order::get_object_or_404($id);
        $order->status_id = 2;
        $order->save();
        $comment = new Comment();
        $comment->content = $datetime;
        $comment->created_at = $now;
        $comment->user_id = $this->user->id;
        $comment->order_id = $order->id;
        $comment->order_history_id = 17;
        $comment->save();
        $this->add_message('success', "Ustalono termin płatności na $datetime");
        if($order->last_user){
            MailView::send(
                'Ustawiono termin platnosci',
                'Ustawiono termin platnosci dla zlecenia #'.$order->id,
                $order->last_user,
                '/dashboard/orders/single?id='.$order->id
            );
        }
        else{
            $admins = User::filter([ ['role_id', '=', 2] ]);
            foreach($admins as $admin){
                MailView::send(
                    'Ustawiono termin platnosci',
                    'Ustawiono termin platnosci dla zlecenia #'.$order->id,
                    $admin,
                    '/dashboard/orders/single?id='.$order->id
                );
            }
        }
    }


}

?>