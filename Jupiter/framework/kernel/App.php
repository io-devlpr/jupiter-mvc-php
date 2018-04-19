<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 2/3/18
 * Time: 12:25 PM
 */

namespace framework\kernel;


// Definition for paths
define("FRAMEWORK_PATH", ROOT_DIR_PATH . DS . "framework" . DS);
define("CORE_PATH", FRAMEWORK_PATH . "core" . DS);
define("DATABASE_PATH",FRAMEWORK_PATH . "database" . DS);
define("KERNEL_PATH", FRAMEWORK_PATH . "kernel" . DS);

define("APP_PATH", ROOT_DIR_PATH . DS . "app" . DS);
define("CONTROLLERS_PATH", APP_PATH . "controllers" . DS);
define("MODELS_PATH", APP_PATH . "models" . DS);
define("VIEWS_PATH" , APP_PATH . "views". DS);

define("PUBLIC_PATH", ROOT_DIR_PATH . DS . "public" . DS);

// definitions of Class paths

define("CONTROLLER_CLASS_PATH", "\\app\\controllers\\");
define("MODEL_CLASS_PATH", "\\app\\model\\");

class App
{
    protected $router;

    protected $timezone;

    public function run(){
        // Configure settings
        $this->configure();

        // Initiate all processes;  Database, Session and Flashes, Cookies, AuthServices, Language
        $this->_initiate();
    }

    private function configure($conf_set = array()){
        $_configure_app = include APP_PATH . "config.php";
        define("ERR_LOG_FILE", ROOT ."tmp" . DS . "log" . DS . $_configure_app['err_log_file']);  // Error file path
        $this->__dev_env_set($_configure_app['development_environment']);        // setting the development environment
        $this->__datetime_zone($_configure_app['datetime']);        // setting the time.
    }

    private function _initiate(){

        // Reset the request check. This is to see if all the request has been passed through
        Route::resetEORCheck();

        $routes = APP_PATH . "route.php";

        if(is_readable($routes))
            include_once $routes;

        Route::ifEORReached();
    }

    /**
     * Toggling btn development environment and end-user use.
     * @example When set to true, error reporting is set to on.. this means when your a programming, you will be able to see the errors,
     *          and when set to false, al errors will be appended on the tmp/log/sys_error.log (default) file.
     * @param boolean that shows either the environment is true or false
     * @return mixed
     */
    public function __dev_env_set($env_dev_token){
        if($env_dev_token){
            // If True

            error_reporting(E_ALL | E_STRICT);
            // When development environment is on, perform error reporting to development
            ini_set("display_errors", 1);
            ini_set("display_startup_errors", 1);
            ini_restore("error_log");
        }else{
            // If False

            error_reporting(E_ALL & ~E_DEPRECATED);
            ini_set("display_errors", 0);
            ini_set("display_startup_errors", 0);
            ini_set("html_errors", 0);
            ini_set("log_errors", 1);
            ini_set("log_errors_max_len", 0);   // No limited amount

            // Setting which file the error is saved to.
            ini_set("error_log", ERR_LOG_FILE);
        }
    }

    /**
     * Given that the timezones change. When moving to install the program to differnt areas. it is mind ful to make sure that the timezone is intact
     * and is similar to the country with which it is installed with. The timezone is very important in making sure that different parts of the system are in sync
     *
     * @param $timezone
     */
    public function __datetime_zone($timezone){
        if($timezone != ""){
            ini_set("date.timezone", date_default_timezone_get());
        }else{

            ini_set("date.timezone", $this->timezone);
        }
    }
}