<?php

require_once __dir__.'/../engine/model.php';
require_once __dir__.'/../engine/fields.php';
require_once __dir__.'/../models/User.php';
require_once __dir__.'/../models/File.php';
require_once __dir__.'/../models/OrderHistory.php';
require_once __dir__.'/../traits/PolishConvert.php';

class Comment extends Model
{
    const TABLE = 'comments';
    use PolishConvert;

    public function __construct()
    {
        $this->content = TextField::init('content', ['max' => 2000]);
        $this->created_at = DateTimeField::init('created_at');
        $this->user_id = ForeignField::init('user_id', User::class);
        $this->order_id = ForeignField::init('order_id', File::class);
        $this->order_history_id = ForeignField::init('order_history_id', OrderHistory::class, ['required' => false]);
    }

    public function get_user(){
        $user = User::get($this->user_id);
        if($user){
            $user->external_user = $user->get_external_user();
        }
        return $user;
    }

    public function get_files(){
        return File::filter([ ['comment_id', '=', $this->id] ]);
    }

    public function get_order_history(){
        return OrderHistory::get($this->order_history_id);
    }
}

?>