<?php

/**
 * The factory class begins with the automatic configuration we recently added to Cache and Configuration.
 * The "server" driver is the only one we will create and it will be returned as an instance of Session\Driver\Server.
 * The Session\Driver class is virtually identical to the Cache\Driver and Configuration\Driver classes. 
 *
 * @author Faizan Ayubi
 */

namespace Framework {

    use Framework\Base as Base;
    use Framework\Registry as Registry;
    use Framework\Session as Session;
    use Framework\Session\Exception as Exception;

    class Session extends Base {
        
        /**
         * @readwrite
         */
        protected $_type;

        /**
         * @readwrite
         */
        protected $_options;

        protected function _getExceptionForImplementation($method) {
            return new Exception\Implementation("{$method} method not implemented");
        }

        protected function _getExceptionForArgument() {
            return new Exception\Argument("Invalid argument");
        }

        public function initialize() {
            $type = $this->getType();
            if (empty($type)) {
                $configuration = Registry::get("configuration");
                if ($configuration) {
                    $configuration = $configuration->initialize();
                    $parsed = $configuration->parse("configuration/session");
                    if (!empty($parsed->session->default) && !empty($parsed->session->default->type)) {
                        $type = $parsed->session->default->type;
                        unset($parsed->session->default->type);
                        $this->__construct(array(
                            "type" => $type,
                            "options" => (array) $parsed->session->default
                        ));
                    }
                }
            }
            if (empty($type)) {
                throw new Exception\Argument("Invalid type");
            }
            switch ($type) {
                case "server": {
                    return new Session\Driver\Server($this->getOptions());
                    break;
                }
                default: {
                    throw new Exception\Argument("Invalid type");
                    break;
                }
            }
        }

    }

}