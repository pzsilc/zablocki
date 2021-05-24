<?php

require_once __dir__.'/../engine/model.php';
require_once __dir__.'/../engine/fields.php';

class Priority extends Model
{
    const TABLE = 'priorities';
    public function __construct()
    {
        $this->name = CharField::init('name', ['max' => 16]);
    }
}

?>