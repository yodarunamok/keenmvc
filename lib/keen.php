<?php
namespace KeenMVC;

/**
 * Created by IntelliJ IDEA.
 * User: Chris Hansen (chris@iviking.org)
 * Date: 1/28/14
 * Time: 2:09 PM
 */

/**
 * Class Singleton
 *
 * Singleton parent class to be extended by singleton classes.
 *
 */
abstract class Singleton
{
    final private static function get($value = null)
    {
        static $staticValue = null;

        if (!is_null($value)) $staticValue = $value;
        return $staticValue;
    }

    final private function __construct()
    {
        // Child classes should be using the instance() method instead
    }

    /**
     * instance
     *
     * Creates an instance of a singleton class, if necessary, and returns it.
     *
     * @return  Singleton|boolean   The singleton class instance, or false if initialization fails.
     */
    public static function instance()
    {
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
    abstract protected function initialize();
}

class App extends Singleton
{
    private $config = array();
    private $namedPaths = array();
    /** @var Path $rootPath */
    private $rootPath;
    public static $request = array();

    protected function initialize($configPath = '../keen_config.ini')
    {
        $this->config = parse_ini_file($configPath, true);
        if ($this->config !== false) {
            $this->rootPath = new Path();
            // now populate the routes if they're in a separate file
            if (isset($this->config['environment']['routes_file_path']) && strlen(trim($this->config['environment']['routes_file_path'])) > 0) {
                require_once $this->config['environment']['routes_file_path'];
            }
            return true;
        }
        return false;
    }

    public static function getConfig($section, $key)
    {
        /** @var App $keen */
        $keen = App::instance();
        return $keen->config[$section][$key];
    }

    public static function route($path, $controllerName, $controllerFile = '', $name = '', $configFile = '')
    {
        /** @var App $keen */
        if (strlen(trim($configFile)) > 0) {
            $keen = App::instance($configFile);
        } else {
            $keen = App::instance();
        }
        if (strlen(trim($name)) > 0) $keen->namedPaths[$name] = $path;
        $pathAsArray = $keen->getArrayFromPath($path);
        if (count($pathAsArray) > $keen->config['environment']['max_path_depth']) {
            Logger::writeErrorAndExit('pathDepth', array($path));
        }
        $keen->rootPath->addRoute($pathAsArray, 0, $controllerName, $controllerFile);
    }

    public function run($isTest = false)
    {
        // grab passed parameters based on request type
        if ($_SERVER['REQUEST_METHOD'] == 'GET' || $_SERVER['REQUEST_METHOD'] == 'POST') {
            $requestArrayName = '_' . strtoupper($_SERVER['REQUEST_METHOD']);
            if (!isset($$requestArrayName)) self::$request = $_REQUEST;
            else self::$request = $$requestArrayName;
        } elseif (($stream = fopen('php://input', "r")) !== false) {
            parse_str(stream_get_contents($stream), self::$request);
        } else {
            self::$request = array();
        }
        // determine what was requested and go get it
        $requestPathArray = $this->getArrayFromPath(str_replace(strrchr($_SERVER['REQUEST_URI'], '?'), '', $_SERVER['REQUEST_URI']));
        $controller = $this->rootPath->getRouteController($requestPathArray, 0);
        if ($controller instanceof Controller) {
            $methodName = strtolower($_SERVER['REQUEST_METHOD']);
            $output = $controller->$methodName();
        } else {
            $output = '';
            $type = (is_object($controller))?get_class($controller):gettype($controller);
            Logger::writeErrorAndExit('badController', array($type));
        }
        // handle page output
        if ($isTest) {
            return $output;
        }
        echo $output;
        exit;
    }

    private function getArrayFromPath($path)
    {
        $path = trim(preg_replace('/\/+/', '/', $path), " \t\n\r\0\x0B/");
        return (strlen($path) > 0)?explode('/', $path):array();
    }
}

class Path
{
    private $childPaths = array();
    private $parameterMap = array();
    private $controllerName;
    private $controllerFile;

    public function __construct ()
    {
    }

    public function addRoute(array $pathAsArray, $currentPosition, $controllerName, $controllerFile, $parameterMap = array())
    {
        if (isset($pathAsArray[$currentPosition])) {
            /** @var Path $childPath */
            if (substr($pathAsArray[$currentPosition], 0, 1) == '@') {
                $parameterMap[$pathAsArray[$currentPosition]] = $currentPosition;
                $pathSegment = '@';
            } else {
                $pathSegment = $pathAsArray[$currentPosition];
            }
            if (! isset($this->childPaths[$pathSegment])) $this->childPaths[$pathSegment] = new Path();
            $childPath = $this->childPaths[$pathSegment];
            $childPath->addRoute($pathAsArray, ++$currentPosition, $controllerName, $controllerFile, $parameterMap);
        } else {
            $this->parameterMap = $parameterMap;
            $this->controllerName = $controllerName;
            $this->controllerFile = $controllerFile;
         }
    }

    public function getRouteController(array $pathAsArray, $currentPosition, array $parameterArray = array())
    {
        if (isset($pathAsArray[$currentPosition])) {
            /** @var Path $childPath */
            if (isset($this->childPaths[$pathAsArray[$currentPosition]])) {
                $childPath = $this->childPaths[$pathAsArray[$currentPosition]];
                return $childPath->getRouteController($pathAsArray, ++$currentPosition, $parameterArray);
            } elseif (isset($this->childPaths['@'])) {
                $childPath = $this->childPaths['@'];
                $parameterArray[$currentPosition] = $pathAsArray[$currentPosition];
                return $childPath->getRouteController($pathAsArray, ++$currentPosition, $parameterArray);
            }
            self::routeNotFound();
            return false;
        } else {
            if (strlen(trim($this->controllerFile)) > 0) {
                $result = include_once($this->controllerFile);
            } else {
                $controllerPath = dirname(__FILE__) . '/' . App::getConfig('environment', 'controllers_path');
                $controllerPath .= '/' . str_replace('_', '/', $this->controllerName) . '.php';
                $result = include_once($controllerPath);
            }
            if ($result) {
                /** @var Controller $controller */
                $controller = new $this->controllerName();
                $controller->initializeParameters($this->parameterMap, $parameterArray);
                return $controller;
            }
        }
        return false;
    }

    final protected static function routeNotFound()
    {
        header('HTTP/1.0 404 Not Found');
        exit;
    }
}

abstract class Controller
{
    protected $pathParameters = array();
    /** @var View $view */
    protected $view;

    public function __construct()
    {
        $viewPath = App::getConfig('environment', 'views_path') . '/' . get_class($this) . '.html';
        $this->view = new View($viewPath);
    }

    public function get()
    {
        self::methodNotImplemented();
    }

    public function put()
    {
        self::methodNotImplemented();
    }

    public function post()
    {
        self::methodNotImplemented();
    }

    public function delete()
    {
        self::methodNotImplemented();
    }

    final public function initializeParameters(array $parameterMap, array $parameterArray)
    {
        foreach ($parameterMap as $name => $position) {
            $this->pathParameters[$name] = $parameterArray[$position];
        }
    }

    protected final static function methodNotImplemented()
    {
        header('HTTP/1.0 501 Not Implemented');
        exit;
    }
}

class View
{
    /** @var \DOMDocument $domDocument */
    private $domDocument;
    private $viewFilePath;

    public function __construct($viewFilePath)
    {
        $this->viewFilePath = $viewFilePath;
        $this->domDocument = false;
    }

    public function parse()
    {
        return $this->getDomDocument()->saveHTML();
    }

    private function getDomDocument()
    {
        if ($this->domDocument === false) {
            $this->domDocument = new \DOMDocument();
            $this->domDocument->loadHTMLFile($this->viewFilePath);
        }
        return $this->domDocument;
    }
}

class Model
{
//    private $data = array();
}

class Logger extends Singleton
{
    private static $errorsArray = array(
        'requestType' => array(
            'head' => 'HTTP/1.0 501 Not Implemented',
            'message' => 'Specified request type not available.  (<<detail1>>)'
        ),
        'pathDepth' => array(
            'head' => 'HTTP/1.0 501 Not Implemented',
            'message' => 'Requested path exceeds maximum path depth.  Check your KeenMVC configuration. (<<detail1>>)'
        ),
        'badController' => array(
            'head' => 'HTTP/1.0 501 Not Implemented',
            'message' => 'Invalid controller specified.  Controller must be a descendant of KeenController. (<<detail1>>)'
        )
    );

    protected function initialize()
    {
        return true;
    }

    public static function writeErrorAndExit($code, array $details)
    {
        error_log(str_replace(array('<<detail1>>', '<<detail2>>'), $details, self::$errorsArray[$code]['message']));
        header(self::$errorsArray[$code]['head']);
        exit;
    }
}
