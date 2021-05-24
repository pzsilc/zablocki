<?php

require_once __dir__.'/../engine/model.php';
require_once __dir__.'/../engine/fields.php';
require_once __dir__.'/../models/User.php';

class Message extends Model
{
    const TABLE = 'messages';
    public function __construct()
    {
        $this->content = CharField::init('content', ['max' => 256]);
        $this->author_id = ForeignField::init('author_id', User::class);
        $this->user_id = ForeignField::init('user_id', User::class);
    }

    public function get_author(){
        $author = User::get($this->author_id);
        if($author){
            $author->external_user = $author->get_external_user();
        }
        return isset($author->external_user) ? $author->external_user->first_name.' '.$author->external_user->last_name : "";
    }

    public static function create_new($author, $user, $content, $order_id)
    {
        $mess = new Message();
        $mess->content = $content;
        $mess->author_id = $author->id;
        $mess->user_id = $user->id;
        $mess->order_id = $order_id;
        $mess->save();
        return $mess->id;
    }
}

?>