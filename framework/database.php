<?php

/**
 * Database factory class, which loads a database driver.
 * The database drivers are a little different from configuration and caching.
 *
 * @author Faizan Ayubi
 */

namespace Framework {

    use Framework\Base as Base;
    use Framework\Database as Database;
    use Framework\Database\Exception as Exception;

    class Database extends Base {

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

        public function initialize() {
            $type = $this->getType();
            
            if (empty($type)) {
                $configuration = Registry::get("configuration");
                if ($configuration) {
                    $configuration = $configuration-> initialize();
                    $parsed = $configuration->parse("configuration/database");
                    if (!empty($parsed->database->default) && !empty($parsed->database->default->type)) {
                        $type = $parsed->database->default->type;
                        unset($parsed->database->default->type);
                        $this->__construct(array("type" => $type, "options" => (array) $parsed->database->default));
                    }
                }
            }
            if (empty($type)) {
                throw new Exception\Argument("Invalid type");
            }
            switch ($type) {
                case "mysql": {
                        return new Database\Connector\Mysql($this->getOptions());
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