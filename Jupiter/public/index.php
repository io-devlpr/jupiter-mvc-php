<?php

define("DS", DIRECTORY_SEPARATOR);

define("ROOT", (__DIR__));

define("ROOT_DIR_PATH", ROOT . DS . "..");

/**
 * @param $_CLASS_NAME
 *
 * This function is going to be used for the autoloading classes in the system.
 */
function _autoload($_CLASS_NAME){
    $_exception_classname = [
        'PDO', 'mysql'
    ];

    // Get the classname

    $actualClassName = $_CLASS_NAME;
    if(strchr($_CLASS_NAME, "\\")){
        $splitClassName = explode("\\", $_CLASS_NAME);
        $actualClassName = $splitClassName[count($splitClassName) - 1];
    }

    $checkIfValidName = true;       // boolean to check if a value fits to be required
    foreach ($_exception_classname as $item){
        if ($item == $actualClassName){
            $checkIfValidName = false;
            break;
        }
    }


    if($checkIfValidName){
        // Sort the class name into an array incase it has \ to it.
        $class_name = strchr($_CLASS_NAME, "\\") ? explode("\\", ltrim($_CLASS_NAME, "\\")) : ucfirst(strtolower($_CLASS_NAME));

        // Fix it so that it has a proper class name depending on the system the server is operating
        // and this is achieved using the DIRECTORY_SEPARATOR

        if(is_array($class_name)){
            $properClassName = implode(DS, $class_name);
        }else{
            $properClassName = $class_name;
        }

        require ROOT_DIR_PATH  . DS . $properClassName . ".php";
    }
}

spl_autoload_register("_autoload");


$app = new \framework\kernel\App();

/*
 * Initializing the system
 */

$app->run();
