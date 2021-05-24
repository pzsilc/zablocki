<?php

require_once __dir__.'/../engine/model.php';
require_once __dir__.'/../engine/fields.php';

class Stage extends Model
{
    const TABLE = 'stages';
    public function __construct()
    {
        $this->name = CharField::init('name', ['max' => 32]);
    }
}

?>