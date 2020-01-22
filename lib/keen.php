<?php
namespace KeenMVC;

use DOMDocument, DOMXPath, DOMNode;
/**
 * Created by IntelliJ IDEA.
 * Version 0.1
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
        // Child classes should be using the load() method instead
    }

    /**
     * load
     *
     * Creates an instance of a singleton class, if necessary, and returns it.
     *
     * @return  Singleton|boolean   The singleton class instance, or false if initialization fails.
     */
    public static function load()
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
    private $routes = array();
    public static $request = array();

    protected function initialize($configPath = 'keen_config.ini.php')
    {
        $this->config = parse_ini_file($configPath, true);
        if (
            $this->config !== false &&
            isset($this->config['environment']['routes_file_path']) &&
            strlen(trim($this->config['environment']['routes_file_path'])) > 0
        ) {
            $this->routes = parse_ini_file($this->config['environment']['routes_file_path'], true);
            return true;
        }
        return false;
    }

    public static function getConfig($section, $key)
    {
        /** @var App $keen */
        $keen = App::load();
        return isset($keen->config[$section][$key])?$keen->config[$section][$key]:null;
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
        // used to be: trim(preg_replace('/\/+/', '/', $path), " \t\n\r\0\x0B/")
        $path = trim(str_replace(array('////', '///', '//'), '/', "/{$_SERVER['REQUEST_URI']}/"), " \t\n\r\0\x0B");
        $param = '';
        if (isset($this->routes['routes'][$path])) {
            // handle exact paths
            $controllerName = $this->routes['routes'][$path];
        } elseif (strlen($path) > 2) {
            // check for and handle paths with trailing parameters
            // right now only one trailing parameter is allowed, but that could be changed if there were a compelling reason
            $param = trim(substr($path, strrpos($path, '/', -2)), '/');
            $path = substr_replace($path, '/@/', -(strlen($param) + 2));
            if (isset($this->routes['routes'][$path])) {
                $controllerName = $this->routes['routes'][$path];
            }
        }
        // if a route matched, we should have a controller, so go get it
        if (isset($controllerName)) {
            include_once "{$this->config['environment']['controllers_path']}/{$controllerName}.php";

            $controller = new $controllerName();
            if ($controller instanceof Controller) {
                $methodName = strtolower((isset(self::$request['_method']))?self::$request['_method']:$_SERVER['REQUEST_METHOD']);
                $output = $controller->$methodName($param);                
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
        // when no route is found, we fall through here
        return self::routeNotFound($isTest);
    }

    protected static function routeNotFound($isTest = false)
    {
        if ($isTest === true) return '';
        header('HTTP/1.0 404 Not Found');
        exit;
    }
}

abstract class Controller
{
    /** @var View $view */
    protected $view;

    public function __construct()
    {
        $viewPath = App::getConfig('environment', 'views_path') . '/' . get_class($this) . '.html';
        $this->view = new View($viewPath);
    }

    public function get($param = null)
    {
        self::methodNotImplemented(get_class($this), 'get', $param);
    }

    public function put($param = null)
    {
        self::methodNotImplemented(get_class($this), 'put', $param);
    }

    public function post($param = null)
    {
        self::methodNotImplemented(get_class($this), 'post', $param);
    }

    public function delete($param = null)
    {
        self::methodNotImplemented(get_class($this), 'delete', $param);
    }

    protected final static function methodNotImplemented($class, $method, $param = null, $forceLog = false)
    {
        if ($param !== null || $forceLog === true) {
            error_log("Unimplemented method ({$method}) in {$class} called with parameter ({$param})");
        }
        header('HTTP/1.0 501 Not Implemented');
        exit;
    }
}

class View
{
    /** @var DOMDocument $domDocument */
    /** @var  DOMXPath $domXpath */
    private $viewFilePath;
    private $domDocument;
    private $domXpath;

    public function __construct($viewFilePath)
    {
        $this->viewFilePath = $viewFilePath;
    }

    public function render($dataArray = array())
    {
        // turn off and clear libxml errors since some HTML5 elements will cause them
        libxml_use_internal_errors(true);
        libxml_clear_errors();
        // we wait to load up the DOM until we're sure we'll need it...
        $this->domDocument = new DOMDocument();
        $result = $this->domDocument->loadHTMLFile($this->viewFilePath);
        if ($result !== true) Logger::writeErrorAndExit('badViewFilePath', array($this->viewFilePath));
        // we'll also need an XPATH object for finding elements
        $this->domXpath = new DOMXPath($this->domDocument);
        $bindingsFile = App::getConfig('environment', 'bindings_file_path');
        // handle data bindings
        if (!is_null($bindingsFile)) {
            $bindings = parse_ini_file($bindingsFile, true);
            foreach ($bindings as $selector => $binding) {
                // before anything, make sure there's data in this binding...
                if (!isset($binding['data'])) continue;
                // figure out what type of selector was used, and handle appropriately
                $firstChar = strtolower(substr(trim($selector), 0, 1));
                if ($firstChar >= 'a' && $firstChar <= 'z') {
                    $elements = $this->domDocument->getElementsByTagName($selector);
                } else {
                    $label = substr($selector, 1);
                    switch ($firstChar) {
                        case '.':
                            $xpath = "//*[@class='{$label}']";
                            break;
                        case '#':
                            $xpath = "//*[@id='{$label}']";
                            break;
                        default:
                            $xpath = $selector;
                        // do nothing here, for now
                    }
                    $elements = $this->domXpath->query($xpath);
                }
                // if we found elements that match a data binding, handle that binding
                if ($elements->length > 0) {
                    // handle binding with arguments and without appropriately...
                    $hasArgs = isset($binding['args']) && strlen(trim($binding['args'])) > 0;
                    $tempData = array($binding['data']);
                    if ($hasArgs) {
                        $argsArray = explode(',', str_replace(' ', '', $binding['args']));
                        foreach ($argsArray as $arg) {
                            $tempData[] = isset($dataArray[$arg])?$dataArray[$arg]:'';
                        }
                    }
                    foreach ($elements as $element) {
                        /** @var DOMNode $element */
                        $dataOut = $hasArgs?call_user_func_array('sprintf', $tempData):$binding['data'];
                        if (isset($binding['is_html']) && $binding['is_html']) {
                            $htmlFragment = $this->domDocument->createDocumentFragment();
                            $htmlFragment->appendXML($dataOut);
                            // unless we configured this binding otherwise, remove the current element's children
                            if (!isset($binding['replace_contents']) || $binding['replace_contents'] === false) {
                                $this->deleteNodeChildren($element);
                            }
                            $element->appendChild($htmlFragment);
                        } else {
                            $element->nodeValue = $dataOut;
                        }
                    }
                }
            }
        }
        // TODO: provide a way to output XML errors for debugging
        // finally, output the HTML
        $result = $this->domDocument->saveHTML();
        if ($result === false) Logger::writeErrorAndExit('unableToGenerateHtml', array($this->viewFilePath));
        return $result;
    }

    /**
     * @param $node DOMNode
     */
    private function deleteNodeChildren($node) {
        while (isset($node->firstChild)) {
            $this->deleteNodeChildren($node->firstChild);
            $node->removeChild($node->firstChild);
        }
    }
}

class Model
{
//    private $data = array();
}

class Logger extends Singleton
{
    private static $errorsArray = array(
        'badViewFilePath' => array(
            'head' => 'HTTP/1.0 500 Internal Server Error',
            'message' => 'Specified view file could not be loaded. (<<detail1>>)'
        ),
        'unableToGenerateHtml' => array(
            'head' => 'HTTP/1.0 500 Internal Server Error',
            'message' => 'Unable to generate HTML from view. (<<detail1>>)'
        ),
        'requestType' => array(
            'head' => 'HTTP/1.0 501 Not Implemented',
            'message' => 'Specified request type not available.  (<<detail1>>)'
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
