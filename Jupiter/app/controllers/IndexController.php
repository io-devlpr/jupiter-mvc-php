<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 2/3/18
 * Time: 12:02 PM
 */

namespace app\controllers;

use framework\core\Controller;

class IndexController extends Controller
{

    public function index(){
        $this->view("home");
    }
    public function action(){
        echo "This is the action page!<br />";
    }

    function show($name, $title, $a){
        echo "<p>$name</p> - <span>$title</span> $a";
    }
    function book($name, $title, $a){
        echo "<h3>$name</h3> - <h5>$title</h5> $a";
    }
}