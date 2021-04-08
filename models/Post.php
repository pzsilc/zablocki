<?php

require_once '../engine/model.php';
require_once '../engine/fields.php';

class Post extends Model
{
    const TABLE = 'posts';
    public function __construct()
    {
        $this->title = CharField::init('title', ['max' => 64, 'default' => '...']);
        $this->description = TextField::init('description', ['max' => 2000, 'required' => false]);
        $this->is_active = BooleanField::init('is_active', ['default' => true]);
        $this->author_id = IntegerField::init('author_id', ['required' => false]);
    }
}

?>