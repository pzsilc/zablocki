<?php

require_once __dir__.'/../engine/model.php';
require_once __dir__.'/../engine/fields.php';
require_once __dir__.'/../models/Order.php';
require_once __dir__.'/../models/Comment.php';

class File extends Model
{
    const TABLE = 'files';
    const PUBLIC_PATH = 'statics/files';

    public function __construct()
    {
        $this->name = CharField::init('name', ['max' => 256]);
        $this->order_id = ForeignField::init('order_id', Order::class, ['required' => false]);
        $this->comment_id = ForeignField::init('comment_id', Comment::class, ['required' => false]);
    }

    //jeśli zwraca int - jest to id, jeśli string - to info o błędzie
    public static function create_new($file, $order_id = null, $comment_id = null)
    {
        $f = new File();
        $new_id = File::max('id') + 1;
        $f->name = $new_id.'.'.microtime(true).'.'.end(explode('.', $file['name']));
        move_uploaded_file($file['tmp_name'], self::PUBLIC_PATH.'/'.$f->name);
        $f->order_id = 0;
        $f->comment_id = 0;
        if($order_id) $f->order_id = $order_id;
        if($comment_id) $f->comment_id = $comment_id;
        $f->save();
        return $f->id;
    }
}

?>