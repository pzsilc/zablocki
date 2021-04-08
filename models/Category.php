<?php

require_once '../engine/model.php';
require_once '../engine/fields.php';

class Category extends Model
{
    const TABLE = 'categories';
    public function __construct()
    {
        $this->name = CharField::init('name', ['max' => 16, 'unique' => true]);
        $this->slug = CharField::init('slug', ['max' => 16, 'unique' => true]);
    }
}

?>