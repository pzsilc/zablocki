<?php
require_once __dir__.'/../engine/view.php';
require_once __dir__.'/../models/Order.php';
require_once __dir__.'/../models/Comment.php';
require_once __dir__.'/../statics/resources/mpdf60/mpdf.php';


class PDFView extends View
{
    public function index()
    {
        $order = Order::get_object_or_404($this->request->get('order_id'));
	$order->assign_relations();
        $order->message = $order->polish_convert_body();
        $order->comments = Comment::sql("select * from comments where order_id=$order->id order by created_at, (case when order_history_id in (6, 8, 9, 17) then 2 else 1 end)");
        foreach($order->comments as $comm){
            $comm->files = $comm->get_files();
            $comm->user = $comm->get_user();
            $comm->order_history = $comm->get_order_history();
            $comm->content = $comm->polish_convert_body();
        }

        $html = "<div style='display: flex; justify-content: between'>
            <h3>ZLECENIE IMPORTOWE</h3>
            <span>Wydruk sporządzono: ".date('Y-m-d H:i:s')."</span>
        </div><br/>";
        $html .= "<br/><b>Zlecenie nr </b> $order->id";
        $html .= "<br/><b>Status: </b>".$order->status->name;
        $html .= "<br/><b>Zgłaszający: </b>".(isset($order->user->external_user) ? $order->user->external_user->first_name.' '.$order->user->external_user->last_name : "BRAK");
        $html .= "<br/><b>Data wysłania zgłoszenia: </b>".$order->created_at;
        $html .= "<br/><b>Data rozpoczęcia zgłoszenia: </b>".$order->get_started_datetime();
        $html .= "<br/><b>Data zakończenia zgłoszenia: </b>".$order->get_ended_datetime();
        $html .= "<br/><b>Wykonawca: </b>".(isset($order->last_user->external_user) ? $order->last_user->external_user->first_name.' '.$order->last_user->external_user->last_name : "BRAK");
        $html .= "<br/><b>Treść zgłoszenia: </b>";
        $html .= "<br/><br/>$order->message";
        $html .= "<br/><hr/>";
        $html .= "<br/>Korespondencja i uwagi:";
        $html .= "<br/><ul>";
        foreach($order->comments as $comm){
            $html .= "<li>
                ".(isset($comm->user->external_user) ? $comm->user->external_user->first_name.' '.$comm->user->external_user->last_name : "BRAK")." --- $comm->created_at
                <br/>";
            if(in_array($comm->order_history_id, [7, 11, 12, 13, 14]))
                $html .= $comm->order_history->name." na $comm->content";
            elseif($comm->order_history_id == 2)
                $html .= $comm->order_history->name." dla $comm->content";
            elseif($comm->order_history_id == 3)
                $html .= $comm->order_history->name.($comm->content ? "<br/>- $comm->content" : "");
            elseif($comm->order_history_id != 0)
                $html .= $comment->order_history->name;
            else
                $html .= $comm->content;
            $html .= "</li>";
        }
        $html .= "</ul>";

        $pdf = new Mpdf();
        $pdf->WriteHTML($html);
        $pdf->Output("PDF-$order->id.pdf", 'D');
    }
}

?>