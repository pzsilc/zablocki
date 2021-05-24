<?php

require_once __dir__.'/../engine/model.php';
require_once __dir__.'/../engine/fields.php';

class Origin extends Model
{
    const TABLE = 'origins';
    public function __construct()
    {
        $this->name = CharField::init('name', ['max' => 32]);
    }
}

?>