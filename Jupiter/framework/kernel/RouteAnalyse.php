<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 2/4/18
 * Time: 5:12 PM
 */

namespace framework\kernel;

use framework\exceptions\CodeException;

class RouteAnalyse
{

    /**
     * @param $URL
     * @param $filter_code
     * @return mixed|null
     *
     * This properly makes the URL (or what ever string is entered)
     */
    public static function parseURL($URL, $filter_code) {
        if(isset($URL)){
            if($URL != "/"){
                return filter_var(rtrim($URL, "/"), $filter_code);
            }else{
                return "/";
            }
        }else{
            return NULL;
        }
    }

    /**
     * @param $url_link
     * @return array|string
     *
     * this breaks down a url (string) using the '/' into an array
     */
    protected static function decomposeURL($url_link){
        $decomposeURL = "";
        if(!empty($url_link)){
            $decomposeURL = explode("/", $url_link);
        }

        return $decomposeURL;
    }

    /**
     * @param $url_link
     * @return string
     *
     * This simply removes the succeeding '/' in a link
     */
    protected static function cleanURL($url_link){
        $cleanURL = "/";
        if(isset($url_link)){
            if($url_link != "/"){
                return (ltrim($url_link, "/"));
            }else{
                return $cleanURL;
            }
        }else{
            return NULL;
        }

//        if(!empty($url_link)){
//            $cleanURL = ltrim($url_link, "/");
//        }
//
//        return $cleanURL;
    }

    /**
     * @param $url_type
     * @return bool|array
     *
     * This function is used in comparing the $url_type with the current URL.
     * if its true, then it stores the routes at $routes
     */
    public static function compareUrlWithTypeAndStore($url_type, $current_url){
        // Function is to compile and select the right url which is compatible with the URL
        $_UTYPE = self::cleanURL(self::parseURL($url_type, FILTER_SANITIZE_STRING));
        $_CURR_URL = self::cleanURL($current_url);

        if($_UTYPE == $_CURR_URL){
            // Checking if they are directly equal
            return true;
        }else{
            if(!empty($_CURR_URL)){
                // Checing if there is any content apart from '/' in the PATH_INFO
                if(strstr($_CURR_URL, "/")){

                    // Filtering the links
                    $_CURL_ARR = self::decomposeURL($_CURR_URL);
                    $_UTYPE_ARR = self::decomposeURL($_UTYPE);

                    $route = [];    // array initiator

                    // Store the routes in an array
                    if(is_array($_UTYPE_ARR)){
                        foreach ($_UTYPE_ARR as $value){
                            if(!strchr($value, "{") && !strstr($value, "}")){
                                $route[] = $value;
                            }
                        }
                    }
                    // check if the routes keywords are in the array.
                    /*
                     * Example for the URL_TYPE = /author/{author}/book/{book-title}
                     * If the user visits the URL (PATH_INFO) = /author/Janet-Gabone/book/Atieno-yo,
                     *
                     * The code first stores the route paths
                     *
                     * i.e. from the URL_TYPE, it stores routes = ['author', 'book'];
                     * and using in_array(route[$i], URL)
                     */

                    $ifNoneIsMissing  = true;
                    $ifNotLackingInNumber = true;

                    // Checks if the all the collected routes are present in the visited URL
                    if(is_array($_UTYPE_ARR)){
                        foreach($route as $collectedRouteVal){
                            if(!in_array($collectedRouteVal, $_CURL_ARR)){
                                $ifNoneIsMissing = false;
                                break;
                            }
                        }
                    }else{
                        $ifNoneIsMissing = false;
                    }

                    // If none is missing
                    if($ifNoneIsMissing){
                        // Check if the array count is the same
                        if(count($_CURL_ARR) != count($_UTYPE_ARR))
                            $ifNotLackingInNumber = false;
                    }

                    // If you have marked the correct linpak
                    if($ifNotLackingInNumber && $ifNoneIsMissing){
                        // Stores the protected routes with the selected ones
                        return $route;
                    }else{
                        return NULL;
                    }
                }
            }

            return NULL;
        }
    }

    /**
     * @param $_URL
     * @param $_UTYPE
     * @param $_ROUTES
     * @return bool
     * @throws CodeException
     *
     * This function is used to compare whether or not the routes have the parameters that matches with there preg matches
     *
     * Consider the following two:
     * 1. Route::get("/{name}/{title}/{joke:\W+}/{games:\w+\:0\d+}", "IndexController@show");
     * 2. Route::get("/{book-name}/{book-author}/{pages:\d+}/{library:\w+\:0\d+}", "IndexController@book");
     *
     * If the user visits the following URL_PATH:
     *
     * james/king-author/23/someplace:01
     *
     * Then it'll be.
     * 1. FALSE
     * 2. TRUE
     *
     * This is because if violates {joke:\W+} for the first one, where the user visits with 23 as a joke variable
     */
    public static function checkIfHasRightParamsType($_URL, $_UTYPE, $_ROUTES){

        $parameters = self::getParameters($_UTYPE, $_URL, $_ROUTES);

        $utypeRoutes = self::decomposeURL((self::cleanURL($_UTYPE)));

        $utypeParameters = [];

        if(empty($parameters)){
           return false;
        }else{
            // Collect the utype parameters
            foreach ($utypeRoutes as $vRoute){
                if(strchr($vRoute, "{") && strchr($vRoute, "}")){
                    $utypeParameters[] = $vRoute;
                }
            }

            if(count($parameters) == count($utypeParameters)){
                $checkValidPregParameter = false;
                $checkValidDefinedPreg = false;

                for($i = 0; $i < count($parameters); $i++){
                    // Find preg;
                    $cleanedTag = $utypeParameters[$i];

                    if($utypeParameters[$i][0] == "{" && $utypeParameters[$i][strlen($utypeParameters[$i]) - 1] == "}"){
                        $cleanedTag = ltrim(rtrim($utypeParameters[$i], "}"), "{");
                    }

                    if(preg_match('@\:+@', $cleanedTag, $matches)){
                        //Trim from the first :
                        /*
                         * For instance the $utypeParameters is book-id:^\d\d\:b\d{2,3}
                         * This means that, it only works for those which have the book-id => 78:b123
                         *
                         * What the code below does is to simple filter only the the regex part or the $utypeParameters
                         *
                         * So, from book-id:^\d\d\:b\d{2,3}, it only gets ^\d\d\:b\d{2,3} and uses it on the preg match for the corresponding URL-paramater ($parameters)
                         */

                        // getting the preg match pattern
                        $colonIndex = strpos($cleanedTag, ":");
                        $length = strlen($cleanedTag) - ($colonIndex);
                        $pattern = ltrim(rtrim(substr($cleanedTag, $colonIndex + 1, $length), "/"), "/");
                        $properPattern = "~\b". $pattern . "\b~";
                        try{
                            if(preg_match($properPattern, $parameters[$i])){
                                $checkValidDefinedPreg = true;
                            }else{
                                break;
                            }
                        }catch (\Exception $e){
                            echo "PregMatchFailed: " . $e->getMessage();
                        }

                    }else{
                        $paramVal = $parameters[$i];
                        // If it has the bad regex values
                        if(preg_match('~\W+~', $paramVal)){
                            // it has bad regex pattern

                            // checks if the only dirty pattern is \- or if there is something else
                            if(strchr($paramVal, "-")){
                                $parsedParamVal = str_replace("-", "", $paramVal);      // removes all '-'

                                // Checks if there is still bad regex characters
                                if(!preg_match('~\W+~', $parsedParamVal)){
                                    // Its clean but with only -
                                    $checkValidPregParameter = true;
                                }else{
                                    // it's dirty and has '-'
                                    break;
                                }
                            }else{
                                // it's dirty and has no '-'
                                break;
                            }
                        }else{
                            // It's clean
                            $checkValidPregParameter = true;
                        }
                    }
                }

                return $checkValidPregParameter && $checkValidDefinedPreg;
            }else{
                return false;
            }
        }
    }

    /**
     * @param $url_type
     * @param $current_url
     * @param $routes
     * @return array|null\
     *
     * This function simply retrieves all the parameters from the visited URL as compared with the urlType
     *
     * So for instance: in the
     * urlType = /hacker/{name}/{age:\d+}
     *
     * and the user visits the path
     * url = /hacker/covert/34
     *
     * the parameters retrieved will be
     * [
     *      [0] => 'covert'
     *      [1] => 34
     * ];
     *
     * it could be refined tho
     */
    public static function getParameters($url_type, $current_url, $routes){
        $parameters = [];       // parameters to be collected

        if(strchr($url_type, "{") && strchr($url_type, "}")){
            // They should have parameters

            $URL = self::decomposeURL((self::cleanURL($current_url)));

            if(!empty($routes)){    // Has routes
                if(!empty($URL)){
                    // collecting parameters when route array is not empty
                    foreach ($URL as $routeOrVar) {
                        if(!in_array($routeOrVar, $routes)){
                            $parameters[] = $routeOrVar;
                        }
                    }
                    return $parameters;
                }else{
                    return null;
                }
            }else{
                // This is for the case the routes are/is empty but the URL has some values
                if(!empty($URL)){
                    foreach ($URL as $routeVal){
                        $parameters[] = $routeVal;
                    }
                    return $parameters;
                }

                return null;
            }
        }
    }


}