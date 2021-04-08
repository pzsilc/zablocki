<?php

require_once '../engine/model.php';
require_once '../engine/fields.php';

class User extends Model
{
    const TABLE = 'users';
    public function __construct()
    {
        $this->name = CharField::init('name', ['max' => 64, 'unique' => true]);
        $this->password = PasswordField::init('password', ['max' => 256]);
        $this->email = EmailField::init('email', ['max' => 64, 'unique' => true]);
    }
}

?>