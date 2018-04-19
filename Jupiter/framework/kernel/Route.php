<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 2/3/18
 * Time: 12:41 PM
 */


namespace framework\kernel;

use framework\exceptions\CodeException;

class Route {

    protected static $routes = [];

    protected static $params = [];

    protected static $default_action = "index";

    protected static $default_controller = "IndexController";

    protected static $u_type = "/";

    protected static $path_already_made = false;

    protected static $link_exists = false;

    protected static $used_link = false;

    protected static $url;    // The current URL the user is in.

    protected static $endReached = false;

    /*
     * You might consider removing it...
     */
    function __construct() {
        echo "Initiated Route";
    }

    static function resetEORCheck(){
        self::$endReached = false;
        self::$link_exists = false;
        self::$used_link = false;
    }

    static function ifEORReached(){
        if(self::$endReached){
            // This means a link is already matched with something
            /* Do Nothing*/
        }else{
            echo "<h2>404! Can't match your link request</h2>";
        }
    }
    /**
     * @param $url_type
     * @param $controller_action
     *
     * This function is used for fetching the URL via the GET Request Method
     */
    static function get($url_type, $controller_action){
        self::getRequestMethod('GET', $url_type, $controller_action);
    }

    /**
     * @param $url_type
     * @param $controller_action
     *
     * This function is used for fetching the URL via the POST Request Method
     */
    static function post($url_type, $controller_action){
        self::getRequestMethod('POST', $url_type, $controller_action);
    }

    /**
     * @param RouteAnalyse $routeAnalyse
     * This function simply cleans and stores the URL inside self::$url
     */
    protected static function storeCurrentURL(RouteAnalyse $routeAnalyse){
        $path_info = isset($_SERVER['PATH_INFO']) ? $_SERVER['PATH_INFO'] : "/";
        self::$url = $routeAnalyse::parseURL($path_info, FILTER_SANITIZE_URL);
    }

    /**
     * @param $identifier
     * @return bool
     *
     * This is function is used to check if the name is a proper sysntax for an identifier
     */
    protected static function isValidIdentifier($identifier){
        $validClassName = true;
        if(preg_match('~^\d~', $identifier)){
            // It starts with a number
            $validClassName = false;
        }else{
            if(preg_match('~\s~', $identifier)){
                // It has white space, tab or new line
                $validClassName = false;
            }else{
                if(!preg_match('~\w~', $identifier)){
                    // It is a not a proper name
                    $validClassName = false;
                }
            }
        }
        return $validClassName;
    }

    protected static function getRequestMethod($method, $url_type, $controller_action){
        self::urlParseAndCheck($url_type, $controller_action);
    }

    protected static function urlParseAndCheck($url_type, $controller_action){
        // add the routes

        // Add last value checkers..

        if(!self::$used_link){
            // It's the first link to make a match
            $routeAnalysis = new RouteAnalyse();

            self::storeCurrentURL($routeAnalysis);        // Store current URL to the static variable

            if(self::validateURLExistance($url_type, $routeAnalysis)){

                if(!self::$link_exists){        // Marking that the link didn't exists and is used for the first time
                    self::$link_exists = true;
                    self::$used_link = true;
                    self::$endReached = true;
                }
            }

            if(self::$link_exists && self::$used_link){
                $checkVal = false;

                if(empty(self::$params) && !(strstr($url_type, "{") && strstr($url_type, "}")))        // If there are no parameters and no '{' or '}' in the URL
                    $checkVal = true;
                if(!empty(self::$params) && (strstr($url_type, "{") && strstr($url_type, "}")))
                    $checkVal = true;

                if($checkVal){
                    // Tag the visited link
                    self::$path_already_made = true;
                    self::$u_type = $url_type;

                    // Checking if the controller_action is actually a function
                    if(is_callable($controller_action)){
                        // It is a function
                        if(!empty(self::$params)){
                            try{
                                call_user_func_array($controller_action, self::$params);
                            }catch (\Exception $e){
                                echo "Function Callback Action Failed: " . $e->getMessage() . " in " . $e->getLine();
                            }
                        }
                    }else{
                        // Its either nothing or a class

                        $cMethod = self::$default_action;         // DEFAULT CONTROLLER ACTION

                        $controller = self::$default_controller;
                        // Split into parts if its a proper class
                        // Ensures there is only one @ sign
                        if(strchr($controller_action, "@")){
                            if(strpos($controller_action, "@") == strrpos($controller_action, "@")){
                                $cComponents = explode("@", $controller_action);

                                // storing the full class name (with directory)
                                $controller = CONTROLLER_CLASS_PATH . $cComponents[0];

                                // Storing the method
                                $cMethod = isset($cComponents[1]) ? $cComponents[1] : self::$default_action;

                            }else{
                                trigger_error("You can't have more than one '@' symbols in your controller_action function");
                            }
                        }else{
                            // Assuming the value entered is a controller only
                            $controller = CONTROLLER_CLASS_PATH . $controller_action;
                        }

                        if(self::isValidIdentifier($controller)){

                            // Checking if the class exists
                            if(class_exists($controller)){
                                if(method_exists($controller, $cMethod)){
                                    if(!empty(self::$params)){
                                        try{
                                            $currentController = new $controller;
                                            call_user_func_array(array($currentController, $cMethod), self::$params);

                                        }catch (\ArgumentCountError $e){
                                            die($e->getMessage() . " in file <b>" . $e->getFile() . "</b> in line <b>" . $e->getLine() . "</b>");
                                        }
                                    }else{
                                        $currentController = new $controller;
                                        $currentController->{$cMethod}();
                                    }
                                }else{
                                    trigger_error("The method at  <b> " . $controller . "::" . $cMethod ."()</b> you are tying to call doesn't exist. Make sure you haven't entered the class file rather than the class name");
                                }
                            }else{
                                trigger_error("The class <b> " . $controller . "</b> you are tying to call doesn't exist. Make sure you haven't entered the class file rather than the class name");
                            }
                        }else{
                            trigger_error("You are trying to initiate a class which doesn't have a proper name (" . $controller . ")");
                        }
                    }
                }
            }

        }

    }

    /**
     * @param $url_type
     * @param RouteAnalyse $routeAnalysis
     * @return bool
     * @throws CodeException
     *
     * This is used for validating if the url the user is visiting is comparable to the ones entered by the programmer in routes.php.
     * At the same time, stores the routes (in self::$routes) and paramaters (in self::$params)
     */
    protected static function validateURLExistance($url_type, RouteAnalyse $routeAnalysis){
        if(!is_null(self::$routes = $routeAnalysis::compareUrlWithTypeAndStore($url_type, self::$url))  && strchr($url_type, "{") && strchr($url_type, "}")){       // Checking if there's a need to ger parameters
            if($routeAnalysis::checkIfHasRightParamsType(self::$url,$url_type, self::$routes)){
                $params = $routeAnalysis::getParameters($url_type, self::$url, self::$routes);       // get parameters from the URL
                self::$params = $params;
                return true;
            }else{
                return false;
            }
        }elseif($routeAnalysis::compareUrlWithTypeAndStore($url_type, self::$url)) {
            return true;
        }else{
            return false;
        }
    }




}