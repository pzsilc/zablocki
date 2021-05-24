<?php

require_once __dir__.'/../engine/model.php';
require_once __dir__.'/../engine/fields.php';
require_once __dir__.'/../models/User.php';
require_once __dir__.'/../models/File.php';
require_once __dir__.'/../models/OrderStatus.php';
require_once __dir__.'/../models/Comment.php';
require_once __dir__.'/../models/Priority.php';
require_once __dir__.'/../models/Stage.php';
require_once __dir__.'/../models/Origin.php';
require_once __dir__.'/../traits/ExternalDatabase.php';
require_once __dir__.'/../traits/PolishConvert.php';


class Order extends Model
{
    const TABLE = 'orders';
    use ExternalDatabase;
    use PolishConvert;

    public function __construct()
    {
        $this->ip = CharField::init('ip', ['max' => 20]);
        $this->message = TextField::init('message', ['max' => 2000]);
        $this->created_at = DateTimeField::init('created_at', ['required' => false]);
        $this->user_id = ForeignField::init('user_id', User::class);
        $this->last_user_id = ForeignField::init('last_user_id', User::class, ['required' => false]);
        $this->status_id = ForeignField::init('status_id', OrderStatus::class, ['default' => 1]);
        $this->priority_id = ForeignField::init('priority_id', Priority::class, ['default' => 1]);
        $this->stage_id = ForeignField::init('stage_id', Stage::class, ['required' => false]);
        $this->origin_id = ForeignField::init('origin_id', Origin::class, ['required' => false, 'default' => 0]);
    }

    //gettery relacji

    public function get_status(){
        return (object)(array)OrderStatus::get($this->status_id);
    }

    public function get_user(){
        $user = User::get($this->user_id);
        if($user){
            $user->external_user = $user->get_external_user();
        }
        return $user;
    }

    public function get_last_user(){
        $user = User::get($this->last_user_id);
        if($user) $user->external_user = $user->get_external_user();
        return $user;
    }

    public function get_files(){
        return File::filter([ ['order_id', '=', $this->id] ]);
    }

    public function get_stage(){
        return Stage::get($this->stage_id);
    }

    public function get_priority(){
        return Priority::get($this->priority_id);
    }

    public function get_comments(){
        return Comment::filter([ ['order_id', '=', $this->id] ]);
    }

    public function get_origin(){
        return Origin::get($this->origin_id);
    }

    //gettery dat zamówienia

    public function get_started_datetime(){
        $dt = Comment::filter([ ['order_id', '=', $this->id], ['order_history_id', '=', 5] ]);
        return $dt ? end($dt)->created_at : '';
    }

    public function get_ended_datetime(){
        $dt = Comment::filter([ ['order_id', '=', $this->id], ['order_history_id', '=', 6] ]);
        return $dt ? end($dt)->created_at : '';
    }

    public function get_scheduled_datetime(){
        $dt = Comment::filter([ ['order_id', '=', $this->id], ['order_history_id', '=', 7] ]);
        return $dt ? end($dt)->content : '';
    }

    public function get_paytime_date(){
        $date = Comment::filter([ ['order_id', '=', $this->id], ['order_history_id', '=', 17] ]);
        return $date ? end($date)->content : '';
    }

    //gettery daty etapów

    public function get_production_date(){
        $dt = Comment::filter([ ['order_id', '=', $this->id], ['order_history_id', '=', 11] ]);
        return $dt ? end($dt)->content : '';
    }

    public function get_logistic_date(){
        $dt = Comment::filter([ ['order_id', '=', $this->id], ['order_history_id', '=', 12] ]);
        return $dt ? end($dt)->content : '';
    }

    public function get_transport_date(){
        $dt = Comment::filter([ ['order_id', '=', $this->id], ['order_history_id', '=', 13] ]);
        return $dt ? end($dt)->content : '';
    }

    public function get_complaint_date(){
        $dt = Comment::filter([ ['order_id', '=', $this->id], ['order_history_id', '=', 14] ]);
        return $dt ? end($dt)->content : '';
    }

    //settery dat zamówienia

    public function set_start_date($date, $user_id){
        $comment = new Comment();
        $comment->order_id = $this->id;
        $comment->content = '';
        $comment->created_at = $date;
        $comment->user_id = $user_id;
        $comment->order_history_id = 5;
        $comment->save();
        return $comment;
    }

    public function set_end_date($date, $user_id){
        $comment = new Comment();
        $comment->order_id = $this->id;
        $comment->content = '';
        $comment->created_at = $date;
        $comment->user_id = $user_id;
        $comment->order_history_id = 6;
        $comment->save();
        return $comment;
    }

    public function set_schedule_date($date, $user_id){
        $comment = new Comment();
        $comment->order_id = $this->id;
        $comment->content = $date;
        $comment->created_at = date('Y-m-d H:i:s');
        $comment->user_id = $user_id;
        $comment->order_history_id = 7;
        $comment->save();
        return $comment;
    }

    public function assign_relations($with_details = true){ //with_details wczytuje informacje o komentarzach i plikach (niezalecane dla indexowej strony)
        $this->user = $this->get_user();
        $this->last_user = $this->get_last_user();
        $this->status = $this->get_status();
        $this->priority = $this->get_priority();
        $this->stage = $this->get_stage();
        $this->origin = $this->get_origin();
        if($with_details)
        {
            $this->comments = $this->get_comments();
            $this->files = $this->get_files();
            $this->stage_dates = [
                'production' => $this->get_production_date(),
                'logistic' => $this->get_logistic_date(),
                'transport' => $this->get_transport_date(),
                'complaint' => $this->get_complaint_date()
            ];
            foreach($this->comments as $comm){
                $comm->files = $comm->get_files();
                $comm->user = $comm->get_user();
                $comm->order_history = $comm->get_order_history();
                $comm->content = $comm->polish_convert_body();
            }
        }
    }
}

?>