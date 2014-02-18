<?php
/**
 * Created by IntelliJ IDEA.
 * User: Chris Hansen (chris@iviking.org)
 * Date: 1/28/14
 * Time: 2:09 PM
 */

/**
 * Class KeenSingleton
 *
 * Singleton parent class to be extended by singleton classes.
 *
 */
abstract class KeenSingleton {
    private final function get ($value = null) {
        static $staticValue = null;

        if(!is_null($value)) $staticValue = $value;
        return $staticValue;
    }

    // Child classes should be using the
    private final function __construct () { }

    /**
     * instance
     *
     * Creates an instance of a singleton class, if necessary, and returns it.
     *
     * @return  KeenSingleton|boolean   The singleton class instance, or false if initialization fails.
     */
    public static function instance () {
        if (static::get() == null) {
            $args = func_get_args();
            $instance = static::get(new static);
            $initResult = call_user_func_array(array($instance, 'initialize'), $args);
            return ($initResult === false)?false:$instance;
        }
        return static::get();
    }

    /**
     * initialize
     *
     * Initializes the class as desired.  While parameters are anticipated, they must have default values.
     *
     * @return boolean
     */
    protected abstract function initialize ();
}

class Keen extends KeenSingleton {
    private $config = array();
    private $namedPaths = array();
    /** @var Path $rootPath */
    private $rootPath;

    protected function initialize ($configPath = '../keen_config.ini') {
        $this->config = parse_ini_file($configPath, true);
        if ($this->config !== false) {
            $this->rootPath = new Path();
            // now populate the routes
            require_once $this->config['environment']['routes_file_path'];
            return true;
        }
        return false;
    }

    public static function addRoute ($type, $path, $controller, $name = '', $configFile = '') {
        /** @var Keen $keen */
        if (strlen(trim($configFile)) > 0) {
            $keen = Keen::instance($configFile);
        }
        else {
            $keen = Keen::instance();
        }
        if (strlen(trim($name)) > 0) $keen->namedPaths[$name] = $path;
        $pathAsArray = $keen->getArrayFromPath($path);
        if (count($pathAsArray) > $keen->config['environment']['max_path_depth']) {
            KeenLogger::writeErrorAndExit(
                'HTTP/1.0 501 Not Implemented',
                "Requested path exceeds maximum path depth.  Check your KeenMVC configuration. ({$path})"
            );
        }
        $keen->rootPath->addRoute(strtolower($type), $pathAsArray, 0, $controller);
    }

    public function run () {}

    private function getArrayFromPath ($path) {
        return explode('/', trim(preg_replace('/\/+/', '/', $path), " \t\n\r\0\x0B/"));
    }
}

class Path {
    private $childPaths = array();
    private $parameterMap = array();
    private $availableTypes = array('get', 'put', 'post', 'delete');

    protected $get, $put, $post, $delete;

    public function __construct () {
        $routeNotFound = function () {
            header('HTTP/1.0 404 Not Found');
            exit;
        };
        $this->get = $routeNotFound;
        $this->put = $routeNotFound;
        $this->post = $routeNotFound;
        $this->delete = $routeNotFound;
    }

    public function addRoute ($type, array $pathAsArray, $currentPosition, $controller, $parameterMap = array()) {
        if (! in_array($type, $this->availableTypes)) {
            KeenLogger::writeErrorAndExit(
                'HTTP/1.0 501 Not Implemented',
                "Specified request type not available.  ({$type})"
            );
        }
        if (isset($pathAsArray[$currentPosition])) {
            /** @var Path $childPath */
            if (substr($pathAsArray[$currentPosition], 0, 1) == '@') {
                $parameterMap[$pathAsArray[$currentPosition]] = $currentPosition;
                $pathSegment = '@';
            }
            else {
                $pathSegment = $pathAsArray[$currentPosition];
            }
            if (! isset($this->childPaths[$pathSegment])) $this->childPaths[$pathSegment] = new Path();
            $childPath = $this->childPaths[$pathSegment];
            $childPath->addRoute($type, $pathAsArray, ++$currentPosition, $controller, $parameterMap);
        }
        else {
            $this->parameterMap[$type] = $parameterMap;
            $this->$type = $controller;
        }
    }
}

class KeenLogger extends KeenSingleton {
    protected function initialize () {
        return true;
    }

    public static function writeErrorAndExit ($headerString, $errorMessage) {
        error_log($errorMessage);
        header($headerString);
        exit;
    }
}

$get = function ($path, $controller, $name = '') {
    Keen::addRoute('get', $path, $controller, $name);
};

$put = function ($path, $controller, $name = '') {
    Keen::addRoute('put', $path, $controller, $name);
};

$post = function ($path, $controller, $name = '') {
    Keen::addRoute('post', $path, $controller, $name);
};

$delete = function ($path, $controller, $name = '') {
    Keen::addRoute('delete', $path, $controller, $name);
};