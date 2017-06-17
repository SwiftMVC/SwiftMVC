<?php
ob_start();
define("DEBUG", TRUE);

// 1. define the default path for includes
define("APP_PATH", str_replace(DIRECTORY_SEPARATOR, "/", dirname(dirname(__FILE__))));
define("URL", "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
define("CDN", "/assets/");

date_default_timezone_set('Asia/Kolkata');

try {
    // 1. load the Core class that includes an autoloader
    require(APP_PATH . "/framework/core.php");
    Framework\Core::initialize();

    // 2. Additional Path's which
    Framework\Core::autoLoadPaths([
        "/application/libraries"
    ]);

    // plugins
    $path = APP_PATH . "/application/plugins";
    $iterator = new DirectoryIterator($path);

    foreach ($iterator as $item) {
        if (!$item->isDot() && $item->isDir()) {
            include($path . "/" . $item->getFilename() . "/initialize.php");
        }
    }

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
        "extension" => !empty($_GET["extension"]) ? $_GET["extension"] : "html"
    ));
    Framework\Registry::set("router", $router);

    // include custom routes 
    include("public/routes.php");

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
        "401" => array(
            "Framework\Router\Exception\Inactive"
        ),
        "404" => array(
            "Framework\Router\Exception\Action",
            "Framework\Router\Exception\Controller"
        ),
        "500" => array(
            "Framework\Cache\Exception",
            "Framework\Configuration\Exception",
            "Framework\Controller\Exception",
            "Framework\Core\Exception",

            "Framework\Database\Exception",
            "Framework\Model\Exception",
            "Framework\Request\Exception",
            "Framework\Router\Exception",
            "Framework\Session\Exception",

            "Framework\Template\Exception",
            "Framework\View\Exception",

            "MongoDB\Driver\Exception\Exception"
        )
    );

    $exception = get_class($e);

    // attempt to find the approapriate template, and render
    foreach ($exceptions as $template => $classes) {
        foreach ($classes as $class) {
            if ($class == $exception || is_subclass_of($exception, $class)) {
                header("Content-type: text/html");
                include(APP_PATH . "/application/views/layouts/errors/{$template}.php");
                exit;
            }
        }
    }

    // log or email any error
    
    // render fallback template
    header("Content-type: text/html");
    include(APP_PATH . "/application/views/layouts/errors/500.php");
    exit;
}
?>
