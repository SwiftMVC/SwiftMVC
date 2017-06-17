<?php

/**
 * Loading libraries and views or integrating with business logic in the model.
 *
 * @author Faizan Ayubi
 */

namespace Framework {

    use Framework\Base as Base;
    use Framework\View as View;
    use Framework\Events as Events;
    use Framework\Registry as Registry;
    use Framework\Template as Template;
    use Framework\Controller\Exception as Exception;

    class Controller extends Base {

        /**
         * @read
         */
        protected $_name;

        /**
         * @readwrite
         */
        protected $_parameters;

        /**
         * @readwrite
         */
        protected $_layoutView;

        /**
         * @readwrite
         */
        protected $_actionView;

        /**
         * @readwrite
         */
        protected $_willRenderLayoutView = true;

        /**
         * @readwrite
         */
        protected $_willRenderActionView = true;

        /**
         * @readwrite
         */
        protected $_defaultPath = "application/views";

        /**
         * @readwrite
         */
        protected $_defaultLayout = "layouts/standard";

        /**
         * @readwrite
         */
        protected $_defaultExtension = "html";

        /**
         * @readwrite
         */
        protected $_defaultContentType = "text/html";

        /**
         * @readwrite
         * @var Container\Request Store the current request state
         */
        protected $_request = null;

        /**
         * It defines the location of the layout template, which is passed to the new View instance, which is then passed into the setLayoutView() setter method.
         * It gets the controller/action names from the router. It gets the router instance from the registry, and uses getters for the names.
         * It then builds a path from the controller/action names, to a template it can render.
         * @param type $options
         */
        public function __construct($options = array()) {
            parent::__construct($options);
            Events::fire("framework.controller.construct.before", array($this->name));

            $router = Registry::get("router");

            switch ($router->getExtension()) {
                case "json":
                    $this->defaultContentType = "application/json";
                    $this->defaultExtension = $router->getExtension();
                    break;

                default:
                    break;
            }

            $this->setLayout();

            if (!$this->request) {
                $this->request = new Container\Request();
            }

            Events::fire("framework.controller.construct.after", array($this->name));
        }

        protected function setLayout($layout = "layouts/standard") {
            $this->defaultLayout = $layout;
            if ($this->willRenderLayoutView) {
                $defaultPath = $this->defaultPath;
                $defaultLayout = $this->defaultLayout;
                $defaultExtension = $this->defaultExtension;

                $view = new View(array(
                    "file" => APP_PATH . "/{$defaultPath}/{$defaultLayout}.{$defaultExtension}"
                ));

                $this->layoutView = $view;
            }

            if ($this->willRenderActionView) {
                $router = Registry::get("router");
                $controller = $router->controller;
                $action = $router->action;

                $view = new View(array(
                    "file" => APP_PATH . "/{$defaultPath}/{$controller}/{$action}.{$defaultExtension}"
                ));

                $this->actionView = $view;
            }
        }

        protected function getName() {
            if (empty($this->_name)) {
                $this->_name = get_class($this);
            }
            return $this->_name;
        }

        protected function _getExceptionForImplementation($method) {
            return new Exception\Implementation("{$method} method not implemented");
        }

        protected function renderJSONFields($data) {
            $obj = array();
            foreach ($data as $key => $value) {
                switch (gettype($value)) {
                    case 'object':
                        if (get_class($value) === "stdClass") {
                            $obj[$key] = $value;
                        } else if (is_a($value, 'Framework\Model')) {
                            $obj[$key] = $value->getJsonData();
                        } else {
                            $obj[$key] = $value;
                        }
                        break;

                    case 'array':
                        $obj[$key] = $this->renderJSONFields($value);
                        break;

                    case 'string':
                    case 'integer':
                    case 'boolean':
                    case 'float':
                    default:
                        $obj[$key] = $value;
                        break;

                }
            }
            return $obj;
        }

        public function render() {
            Events::fire("framework.controller.render.before", array($this->name));

            $defaultContentType = $this->defaultContentType;
            $results = null;

            $doAction = $this->willRenderActionView && $this->actionView;
            $doLayout = $this->willRenderLayoutView && $this->layoutView;

            try {
                if ($doAction) {
                    $view = $this->actionView;

                    $api = $this->request->header('x-json-api', false);
                    if ($this->defaultExtension == "json" && $api == 'SwiftMVC') {
                        $obj = array();
                        $data = $view->data;

                        if ($data) {
                            $obj = $this->renderJSONFields($data);
                        } else {
                            $obj = array();
                        }
                        echo json_encode($obj, JSON_PRETTY_PRINT);
                    } else if ($this->defaultExtension == "json") { // redirect to html
                        $parsed = parse_url(URL);
                        $path = explode(".", $parsed['path'])[0];
                        header("Location: $path");
                        exit();
                    }

                    $results = $view->render();
                    $this
                            ->actionView
                            ->template
                            ->implementation
                            ->set("action", $results);
                }

                if ($doLayout) {
                    $view = $this->layoutView;
                    $results = $view->render();

                    header("Content-type: {$defaultContentType}");
                    echo $results;
                } else if ($doAction) {
                    header("Content-type: {$defaultContentType}");
                    echo $results;
                }

                $this->willRenderLayoutView = false;
                $this->willRenderActionView = false;
            } catch (\Exception $e) {
                throw new View\Exception\Renderer("Invalid layout/template syntax");
            }

            Events::fire("framework.controller.render.after", array($this->name));
        }

        public function __destruct() {
            Events::fire("framework.controller.destruct.before", array($this->name));

            $this->render();

            Events::fire("framework.controller.destruct.after", array($this->name));
        }

    }

}