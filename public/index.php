<?php

// 1. define the default path for includes
define("DEBUG", TRUE);
define("APP_PATH", dirname(dirname(__FILE__)));

try {
    // 2. load the Core class that includes an autoloader
    require("../framework/core.php");
    Framework\Core::initialize();

    // 3. load and initialize the Configuration class 
    $configuration = new Framework\Configuration(array(
        "type" => "ini"
    ));
    Framework\Registry::set("configuration", $configuration->initialize());

    // 4. load and initialize the Database class – does not connect
    $database = new Framework\Database();
    Framework\Registry::set("database", $database->initialize());

    // 5. load and initialize the Cache class – does not connect
    $cache = new Framework\Cache();
    Framework\Registry::set("cache", $cache->initialize());

    // 6. load and initialize the Session class 
    $session = new Framework\Session();
    Framework\Registry::set("session", $session->initialize());

    // 7. load the Router class and provide the url + extension
    $router = new Framework\Router(array(
        "url" => isset($_GET["url"]) ? $_GET["url"] : "home/index",
        "extension" => isset($_GET["url"]) ? $_GET["url"] : "html"
    ));
    Framework\Registry::set("router", $router);
    
    // include custom routes 
    include("routes.php");

    // 8. dispatch the current request 
    $router->dispatch();

    // 9. unset global variables
    unset($configuration);
    unset($database);
    unset($cache);
    unset($session);
    unset($router);
} catch (Exception $e) {
    // list exceptions
    $exceptions = array(
        "500" => array(
            "Framework\Cache\Exception",
            "Framework\Cache\Exception\Argument",
            "Framework\Cache\Exception\Implementation",
            "Framework\Cache\Exception\Service",
            "Framework\Configuration\Exception",
            "Framework\Configuration\Exception\Argument",
            "Framework\Configuration\Exception\Implementation",
            "Framework\Configuration\Exception\Syntax",
            "Framework\Controller\Exception",
            "Framework\Controller\Exception\Argument",
            "Framework\Controller\Exception\Implementation",
            "Framework\Core\Exception",
            "Framework\Core\Exception\Argument",
            "Framework\Core\Exception\Implementation",
            "Framework\Core\Exception\Property",
            "Framework\Core\Exception\ReadOnly",
            "Framework\Core\Exception\WriteOnly",
            "Framework\Database\Exception",
            "Framework\Database\Exception\Argument",
            "Framework\Database\Exception\Implementation",
            "Framework\Database\Exception\Service",
            "Framework\Database\Exception\Sql",
            "Framework\Model\Exception",
            "Framework\Model\Exception\Argument",
            "Framework\Model\Exception\Connector",
            "Framework\Model\Exception\Implementation",
            "Framework\Model\Exception\Primary",
            "Framework\Model\Exception\Type",
            "Framework\Model\Exception\Validation",
            "Framework\Request\Exception",
            "Framework\Request\Exception\Argument",
            "Framework\Request\Exception\Implementation",
            "Framework\Request\Exception\Response",
            "Framework\Router\Exception",
            "Framework\Router\Exception\Argument",
            "Framework\Router\Exception\Implementation",
            "Framework\Session\Exception",
            "Framework\Session\Exception\Argument",
            "Framework\Session\Exception\Implementation",
            "Framework\Template\Exception",
            "Framework\Template\Exception\Argument",
            "Framework\Template\Exception\Implementation",
            "Framework\Template\Exception\Parser",
            "Framework\View\Exception",
            "Framework\View\Exception\Argument",
            "Framework\View\Exception\Data",
            "Framework\View\Exception\Implementation",
            "Framework\View\Exception\Renderer",
            "Framework\View\Exception\Syntax"
        ),
        "404" => array(
            "Framework\Router\Exception\Action",
            "Framework\Router\Exception\Controller"
        )
    );

    $exception = get_class($e);

    // attempt to find the approapriate template, and render
    foreach ($exceptions as $template => $classes) {
        foreach ($classes as $class) {
            if ($class == $exception) {
                header("Content-type: text/html");
                include(APP_PATH . "/application/views/errors/{$template}.php");
                exit;
            }
        }
    }
    
    // log or email any error

    // render fallback template
    header("Content-type: text/html");
    echo "An error occurred.";
    exit;
}
?>