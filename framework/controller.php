<?php

/**
 * Loading libraries and views or integrating with business logic in the model.
 *
 * @author Faizan Ayubi
 */

namespace Framework {

    use Framework\Base as Base;
    use Framework\View as View;
    use Framework\Registry as Registry;
    use Framework\Template as Template;
    use Framework\Controller\Exception as Exception;

    class Controller extends Base {

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
         * It defines the location of the layout template, which is passed to the new View instance, which is then passed into the setLayoutView() setter method.
         * It gets the controller/action names from the router. It gets the router instance from the registry, and uses getters for the names.
         * It then builds a path from the controller/action names, to a template it can render.
         * @param type $options
         */
        public function __construct($options = array()) {
            parent::__construct($options);
            if ($this->getWillRenderLayoutView()) {
                $defaultPath = $this->getDefaultPath();
                $defaultLayout = $this->getDefaultLayout();
                $defaultExtension = $this->getDefaultExtension();
                $view = new View(array(
                    "file" => APP_PATH."/{$defaultPath}/{$defaultLayout}.{$defaultExtension}"
                ));
                $this->setLayoutView($view);
            }
            if ($this->getWillRenderLayoutView()) {
                $router = Registry::get("router");
                $controller = $router->getController();
                $action = $router->getAction();
                $view = new View(array(
                    "file" => APP_PATH."/{$defaultPath}/{$controller}/{$action}.{$defaultExtension}"
                ));
                $this->setActionView($view);
            }
        }

        protected function _getExceptionForImplementation($method) {
            return new Exception\Implementation("{$method} method not implemented");
        }

        protected function _getExceptionForArgument() {
            return new Exception\Argument("Invalid argument");
        }

        public function render() {
            $defaultContentType = $this->getDefaultContentType();
            $results = null;
            $doAction = $this->getWillRenderActionView() && $this->getActionView();
            $doLayout = $this->getWillRenderLayoutView() && $this->getLayoutView();
            try {
                if ($doAction) {
                    $view = $this->getActionView();
                    $results = $view->render();
                } if ($doLayout) {
                    $view = $this->getLayoutView();
                    $view->set("template", $results);
                    $results = $view->render();
                    header("Content-type: {$defaultContentType}");
                    echo $results;
                } else if ($doAction) {
                    header("Content-type: {$defaultContentType}");
                    echo $results;
                    $this->setWillRenderLayoutView(false);
                    $this->setWillRenderActionView(false);
                }
            } catch (\Exception $e) {
                throw new View\Exception\Renderer("Invalid layout/template syntax");
            }
        }

        public function __destruct() {
            $this->render();
        }

    }

}