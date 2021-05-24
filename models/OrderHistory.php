<?php

require_once __dir__.'/../engine/model.php';
require_once __dir__.'/../engine/fields.php';

class OrderHistory extends Model
{
    const TABLE = 'order_histories';
    public function __construct()
    {
        $this->name = CharField::init('name', ['max' => 40]);
        $this->placeholder = CharField::init('placeholder', ['max' => 64, 'required' => false]);
    }
}

?>