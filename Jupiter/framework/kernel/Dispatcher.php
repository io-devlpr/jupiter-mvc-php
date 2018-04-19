<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 2/4/18
 * Time: 5:12 PM
 */

namespace framework\kernel;


class Dispatcher
{
    protected $route;

    function __construct(Route $route)
    {
        $this->route = $route;
    }

    function checkFileExist($filename){

    }
}