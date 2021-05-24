<?php
require_once __dir__.'/../engine/view.php';
require_once __dir__.'/../models/Order.php';
require_once __dir__.'/../models/Comment.php';
require_once __dir__.'/../traits/ExternalDatabase.php';
require_once __dir__.'/../vendor/autoload.php';


class StatisticView extends View
{
    const HIST_LABELS = ['poniżej 1', '1-2', '2-5', '5-8', '8-12', '12-18', '18-30', 'powyżej 30'];
    const MONTHS = ['styczeń', 'luty', 'marzec', 'kwiecień', 'maj', 'czerwiec', 'lipiec', 'sierpień', 'wrzesień', 'październik', 'listopad', 'grudzień'];
    const SECS_IN_DAY = 86400;
    use ExternalDatabase;


    public function __construct()
    {
        parent::__construct();
        $user = $this->request->session('import_auth');
        $user->role->id = @intval(array_shift(array_values((array)($user->role))));
        if(!$user || $user->role->id == 1 || $user->role->id == 3){
            return $this->redirect('/');
        }
        $this->user = $user;
    }




    //liczy średnie dla wykresu liniowego, tzn. na przestrzeni pół roku
    private function prepare_plot_of_totals($orders)
    {
        $now = date('Y-m-d H:i:s');
        $count_previous_months = 12;
        $samples = array_fill(0, $count_previous_months, []);
        foreach($orders as $order)
        {
            //liczenie różnicy dat (w miesiącach)
            $ts1 = strtotime($now);
            $ts2 = strtotime($order->created_at);
            $year1 = date('Y', $ts1);
            $year2 = date('Y', $ts2);
            $month1 = date('m', $ts1);
            $month2 = date('m', $ts2);
            $diff = abs((($year2 - $year1) * 12) + ($month2 - $month1));
            if($diff < $count_previous_months)
            {
                //zamówienie sprzed mniej niż $count_previous_months (tzn. 12 miesięcy)
                $days_of_process = (strtotime($order->total_ended_at) - strtotime($order->created_at)) / (60 * 60 * 24);
                array_push($samples[$diff], $days_of_process);
            }
        }

        $data = Array();
        for($i = 0; $i < $count_previous_months; $i++){
            $month_nb = intval(date('m', strtotime("-$i months")))-1;
            $month_name = self::MONTHS[$month_nb];
            try{
                $data[$month_name] = round(array_sum($samples[$i]) / count($samples[$i]), 2);
            }
            catch(DivisionByZeroError $e){
                $data[$month_name] = 0;
            }
        }

        return $data;
    }





    public function generate()
    {
        $orders_started = Order::sql("
            SELECT 
                o.id, 
                o.created_at, 
                c.created_at as started_at
            FROM orders o 
            LEFT JOIN comments c ON o.id=c.order_id 
            WHERE c.order_history_id=5 AND c.created_at <> '0000-00-00 00:00:00'
            GROUP BY o.id
        ");
        $orders_ended = Order::sql("
            SELECT 
                o.id, 
                o.created_at, 
                c.created_at as ended_at
            FROM orders o 
            LEFT JOIN comments c ON o.id=c.order_id 
            WHERE c.order_history_id=6 AND c.created_at <> '0000-00-00 00:00:00'
            GROUP BY o.id
        ");
        $orders_total_ended = Order::sql("
            SELECT 
                o.id, 
                o.created_at, 
                c.created_at as total_ended_at
            FROM orders o 
            LEFT JOIN comments c ON o.id=c.order_id 
            WHERE (c.order_history_id=6 OR c.order_history_id=8 OR c.order_history_id=9) AND c.created_at <> '0000-00-00 00:00:00'
            GROUP BY o.id
        ");

        $total_num = Order::count();
        $secs_in_day = self::SECS_IN_DAY;
        //czasy rozpoczęcia
        $start_times = array_map(function($order) use ($secs_in_day){ return (strtotime($order->started_at) - strtotime($order->created_at)) / $secs_in_day; }, $orders_started); 
        //czasy zakończenia
        $end_times = array_map(function($order) use ($secs_in_day){ return (strtotime($order->ended_at) - strtotime($order->created_at)) / $secs_in_day; }, $orders_ended);
        //czasy całkowitego zakończenia
        $ttoal_end_times = array_map(function($order) use ($secs_in_day){ return strtotime($order->total_ended_at) - strtotime($order->created_at) / $secs_in_day; }, $orders_total_ended);

        try
        {
            $start_average = round((array_sum($start_times) / count($start_times)), 2);
            $end_average = round((array_sum($end_times) / count($end_times)), 2);
        }
        catch(DivisionByZero $e)
        {
            $this->add_message('error', 'Zbyt mało informacji by móc utworzyć statystykę');
            return $this->redirect('/dashboard');
        }

        //wykres całkowitych czasów
        $plot = $this->prepare_plot_of_totals($orders_total_ended);

        //histogramy
        $start_hist = array_fill(0, count(self::HIST_LABELS), 0);
        $end_hist = array_fill(0, count(self::HIST_LABELS), 0);
        foreach($start_times as $st){
            if($st < 1) $start_hist[0]++;
            elseif($st >= 1 && $st < 2) $start_hist[1]++;
            elseif($st >= 2 && $st < 5) $start_hist[2]++;
            elseif($st >= 5 && $st < 8) $start_hist[3]++;
            elseif($st >= 8 && $st < 12) $start_hist[4]++;
            elseif($st >= 12 && $st < 18) $start_hist[5]++;
            elseif($st >= 18 && $st < 30) $start_hist[6]++;
            else $start_hist[7]++;
        }
        foreach($end_times as $et){
            if($et < 1) $end_hist[0]++;
            elseif($et >= 1 && $et < 2) $end_hist[1]++;
            elseif($et >= 2 && $et < 5) $end_hist[2]++;
            elseif($et >= 5 && $et < 8) $end_hist[3]++;
            elseif($et >= 8 && $et < 12) $end_hist[4]++;
            elseif($et >= 12 && $et < 18) $end_hist[5]++;
            elseif($et >= 18 && $et < 30) $end_hist[6]++;
            else $end_hist[7]++;
        }
        
        echo json_encode([
            'total_num' => $total_num,
            'start_times' => $start_times,
            'end_times' => $end_times,
            'start_average' => $start_average,
            'end_average' => $end_average,
            'start_hist' => $start_hist,
            'end_hist' => $end_hist,
            'plot_of_totals' => $plot,
            'hist_labels' => self::HIST_LABELS
        ], JSON_UNESCAPED_UNICODE);
    }



    public function index()
    {
        return $this->render('dashboard.statistics');
    }



    public function generate_xlsx()
    {
        $orders = Order::all();
        $orders = Order::sql("
            select 
                o.id,
                o.user_id,
                o.created_at,
                c1.content as scheduled_date,
                c2.created_at as ended_at
            from orders as o
            left join comments as c1 on o.id=c1.order_id
            left join comments as c2 on o.id=c2.order_id
            where c1.order_history_id=7 and (c2.order_history_id=6 or c1.order_history_id=8 or c1.order_history_id=9)
            group by o.id
        ");

        $categories = [
            ['title' => '1-10 dni', 'data' => []],
            ['title' => '10-20 dni', 'data' => []],
            ['title' => '20-30 dni', 'data' => []],
            ['title' => 'powyżej 30 dni', 'data' => []]
        ];
        $data = [];
        
        foreach($orders as $order){
            $order->late_days = (strtotime($order->ended_at) - strtotime($order->scheduled_date.' 00:00:00')) / self::SECS_IN_DAY;
        }

        $header = ['<b>ID</b>', '<b>Data zaplanowania</b>', '<b>Data zakończenia</b>', '<b>Czas opóźnienia</b>'];
        foreach($orders as $order){
            if($order->late_days > 0){
                if($order->late_days <= 10)
                    array_push($categories[0]['data'], [$order->id, $order->scheduled_date, $order->ended_at, round($order->late_days, 2).' dni']);
                elseif($order->lats_days > 10 && $order->late_days <= 20)
                    array_push($categories[1]['data'], [$order->id, $order->scheduled_date, $order->ended_at, round($order->late_days, 2).' dni']);
                elseif($order->last_days > 20 && $order->late_days <= 30)
                    array_push($categories[2]['data'], [$order->id, $order->scheduled_date, $order->ended_at, round($order->late_days, 2).' dni']);
                elseif($order->late_days > 30)
                    array_push($categories[3]['data'], [$order->id, $order->scheduled_date, $order->ended_at, round($order->late_days, 2).' dni']);
            }
        }

        foreach($categories as $cat){
            array_push($data, ['Zamówienia opóźnione: '.$cat['title']]);
            array_push($data, $header);
            foreach($cat['data'] as $sample)
                array_push($data, $sample);
        }

        array_push($data, [], [], [], ['<b>Podział na działy</b>', '<b>Liczba zleceń</b>']);

        $sections = $this->external_query("select * from sections");
        for($i=0; $i<count($sections); $i++){
            $sections[$i] = (object)$sections[$i];
            $sections[$i]->orders_num = 0;
        }
        $orders = Order::sql("select o.id, u.external_user_id as eui from orders o left join users u on u.id=o.user_id");
        foreach($orders as $order){
            $order->external_user = $this->external_query('select section_id from people where id='.$order->eui);
            if($order->external_user)
            {
                foreach($sections as $section){
                    if($order->external_user[0]['section_id'] == $section->id) $section->orders_num++;
                }
            }
        }

        foreach($sections as $sec){
            array_push($data, [$sec->name, $sec->orders_num]);
        }

        $path = 'statics/resources/temp.xlsx';
        SimpleXLSXGen::fromArray($data)->saveAs($path);
        if(file_exists($path))
        {
            ob_end_clean();
            header('Content-Description: File Transfer');
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment; filename="'.basename($path).'"');
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate');
            header('Pragma: public');
            header('Content-Length: ' . filesize($path));
            readfile($path);
            exit;
        }
    }
}

?>