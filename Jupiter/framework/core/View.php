<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 2/23/18
 * Time: 9:23 PM
 */

namespace framework\core;


class View
{
    protected static $default = "home";

    public static function make($view_file = null){
        $vfile = self::$default . ".php";
        if(!is_null($view_file)){
            if(strchr($view_file, "*")) {
                $vfile = explode("*", $view_file);
                $vfile = implode(DS, $vfile) . ".php";
            }else{
                $vfile = $view_file . ".php";
            }
        }
        $view_get_file = VIEWS_PATH . $vfile;
        if(file_exists($view_get_file)){
            require_once $view_get_file;
        }else{
            echo "Nope";
        }
    }
}