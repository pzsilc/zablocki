<?php

require_once __dir__.'/../engine/model.php';
require_once __dir__.'/../engine/fields.php';

class UserRole extends Model
{
    const TABLE = 'user_roles';
    public function __construct()
    {
        $this->name = CharField::init('name', ['max' => 32]);
    }
}

?>