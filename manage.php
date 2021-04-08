<?php

final class Manager
{
    const OPERATIONS = [
        ['migrate', 'migrate'],
        ['model', 'create_model'],
        ['controller', 'create_controller'],
        ['form', 'create_form']
    ];

    public static function run($argv)
    {
        $operation = $argv[1];
        foreach(self::OPERATIONS as $o){
            if($o[0] === $operation){
                $method = $o[1];
                return self::$method($argv);
            }
        }
        echo "Such option does not exist";
    }

    public static function migrate($argv)
    {
        if(count($argv) !== 2) throw new Exception('Migrate option is not accepting any parameters');
        $output = shell_exec("cd engine && php migrate.php");
        print_r($output);
    }

    public static function create_model($argv)
    {
        if(count($argv) !== 3) throw new Exception('Model option is accepting exctly 1 param');
        $name = $argv[2];
        $table = strtolower($name).'s';
        $source = file_get_contents('engine/source_templates/model.txt');
        $source = str_replace('__NAME__', $name, $source);
        $source = str_replace('__TABLE__', $table, $source);
        $file = fopen("models/$name.php", 'w');
        fwrite($file, $source);
        fclose($file);
        echo "Model $name created successfuly";
    }

    public static function create_controller($argv)
    {
        if(count($argv) !== 3) throw new Exception('Controller option is accepting exctly 1 param');
        $name = $argv[2];
        $source = file_get_contents('engine/source_templates/controller.txt');
        $source = str_replace('__NAME__', $name, $source);
        $file = fopen("controllers/$name.php", 'w');
        fwrite($file, $source);
        fclose($file);
        echo "Controller $name created successfuly";
    }

    public static function create_form($argv)
    {
        if(count($argv) !== 3) throw new Exception('Form option is accepting exctly 1 param');
        $name = $argv[2];
        $model = str_replace('Form', '', $name);
        $source = file_get_contents('engine/source_templates/form.txt');
        $source = str_replace('__NAME__', $name, $source);
        $source = str_replace('__MODEL__', $model, $source);
        $file = fopen("forms/$name.php", 'w');
        fwrite($file, $source);
        fclose($file);
        echo "Form $name created successfuly";
    }
}

if(count($argv) == 1) throw new Exception('Manager require at least 1 parameter');
Manager::run($argv);

?>