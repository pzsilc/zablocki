<?php
require_once('engine/form.php');
require_once('models/Example.php');

class ExampleForm extends Form
{
    const MODEL = Example::class;

    public function custom()
    {
        //
    }
}

?>