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

            $controller = new $controllerName($isTest);
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
        if ($isTest === true) return '404';
        header('HTTP/1.0 404 Not Found');
        exit;
    }
}

abstract class Controller
{
    /** @var View $view */
    protected $view = false;
    protected $data = array();
    private $isTest;

    public function __construct($isTest = false)
    {
        $this->isTest = $isTest;
        $viewPath = App::getConfig('environment', 'views_path') . '/' . get_class($this) . '.html';
        // TODO: How do we handle situations where there is no view, but someone attempts to use it?
        $this->view = new View($this, $viewPath);
        if (($dataPath = App::getConfig("environment", "template_data_path")) !== null) {
            if (@$dataArray = include $dataPath) {
                // TODO: There should be an error if $dataArray is not an array
                $this->data = $dataArray;
            } else {
                // TODO: There should be an error if the file specified as $dataPath doesn't exist
            }
        }
    }

    public function get($param = null)
    {
        return self::methodNotImplemented(get_class($this), 'get', $param, false, $this->isTest);
    }

    public function put($param = null)
    {
        return self::methodNotImplemented(get_class($this), 'put', $param, false, $this->isTest);
    }

    public function post($param = null)
    {
        return self::methodNotImplemented(get_class($this), 'post', $param, false, $this->isTest);
    }

    public function delete($param = null)
    {
        return self::methodNotImplemented(get_class($this), 'delete', $param, false, $this->isTest);
    }

    public function addControllerDatum($cssIdentifier, $rawValue, $replace="", $isHtml=false, $replaceContents=true)
    {
        $this->data[$cssIdentifier] = ["raw_value" => $rawValue, "replace" => $replace, "is_html" => $isHtml, "replace_contents" => $replaceContents];
    }

    public function getControllerData()
    {
        return $this->data;
    }
 
    protected final static function internalServerError($class, $method, $message, $isTest = false)
    {
        error_log("Internal Server Error in method ({$method}) of {$class}!");
        error_log("Error Details: {$message}");
        if ($isTest === true) return "500";
        header('HTTP/1.0 500 Internal Server Error');
        exit;
    }

    protected final static function methodNotImplemented($class, $method, $param = null, $forceLog = false, $isTest = false)
    {
        if ($param !== null || $forceLog === true) {
            error_log("Unimplemented method ({$method}) in {$class} called with parameter ({$param})");
        }
        if ($isTest === true) return '501';
        header('HTTP/1.0 501 Not Implemented');
        exit;
    }
}

class View
{
    /** @var Controller             $controller */
    /** @var string                 $viewFilePath */
    /** @var DOMDocument            $domDocument */
    /** @var DOMXPath               $domXpath */
    /** @var CSS2XPath\Translator   $c2xTranslator */
    /** @var array                  $pageData */
    private $controller;
    private $viewFilePath;
    private $domDocument;
    private $domXpath;
    private $c2xTranslator;
    private $pageData;

    /**
     * View constructor. Takes as its only parameter the path to the HTML file that is the basis of the view.
     * 
     * @param   Controller  $controller
     * @param   string      $viewFilePath
     */
    public function __construct($controller, $viewFilePath)
    {
        $this->controller = $controller;
        $this->viewFilePath = $viewFilePath;
    }

    public function render()
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
        // Get the data
        $this->pageData = $this->controller->getControllerData();
        // handle templating
        foreach ($this->pageData as $selector => $templateData) {
            $this->findAndSetElements($selector, $templateData);
        }
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

    private function findAndSetElements($selector, $templateData) {
        // before anything, make sure there's associated data...
        if (!isset($templateData["raw_value"])) return;
        // first check for and process any simple tag passed, otherwise convert any passed CSS identifier to XPath
        if (preg_match('/^-?[_a-zA-Z]+[_a-zA-Z0-9-]*$/', $selector) === 1) {
            $elements = $this->domDocument->getElementsByTagName($selector);
        } else {
            require_once "css2xpath/Translator.php";
            $this->c2xTranslator = new CSS2XPath\Translator();
            $xpath = $this->c2xTranslator->translate($selector);
            $elements = $this->domXpath->query($xpath);
        }
        // if we found elements that match, handle those elements
        if ($elements->length > 0) {
            // handle elements both with and without replacement data
            $hasReplacement = isset($templateData["replace"]) && (is_array($templateData["replace"]) || strlen(trim($templateData["replace"])) > 0);
            $tempData = array($templateData["raw_value"]);
            if ($hasReplacement) {
                // TODO: there should be an error if $elementData["replace"] is an object
                if (!is_array($templateData["replace"])) {
                    $templateData["replace"] = array($templateData["replace"]);
                }
                foreach ($templateData["replace"] as $arg) {
                    $tempData[] = $arg;
                }
            }
            foreach ($elements as $element) {
                /** @var DOMNode $element */
                if (isset($templateData["type"]) && strtolower($templateData["type"]) == "file") {
                    if (isset($templateData["use_include_path"])) $tempData[] = $templateData["use_include_path"];
                    $dataOut = call_user_func_array("file_get_contents", $tempData);
                } else {
                    $dataOut = $hasReplacement?call_user_func_array("sprintf", $tempData):$templateData["raw_value"];
                }
                if (isset($templateData["is_html"]) && $templateData["is_html"]) {
                    $htmlFragment = $this->domDocument->createDocumentFragment();
                    $htmlFragment->appendXML($dataOut);
                    // unless we configured these elements otherwise, remove the current element's children
                    if (!isset($templateData["replace_contents"]) || $templateData["replace_contents"] === true) {
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
