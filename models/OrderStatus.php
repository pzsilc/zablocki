<?php

require_once __dir__.'/../engine/model.php';
require_once __dir__.'/../engine/fields.php';

class OrderStatus extends Model
{
    const TABLE = 'order_statuses';
    public function __construct()
    {
        $this->name = CharField::init('name', ['max' => 40]);
        $this->color = CharField::init('color', ['max' => 7]);
    }
}

?>