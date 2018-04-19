<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 2/3/18
 * Time: 11:59 AM
 */

namespace framework\core;

use framework\core\View as View;

abstract class Controller
{
       protected function view($views_file){
            View::make($views_file);
       }
}