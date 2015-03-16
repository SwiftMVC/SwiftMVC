<?php

/**
 * Connecting to the database engine and/or returning the relevant query class instances.
 *
 * @author Faizan Ayubi
 */

namespace Framework\Database {

    use Framework\Base as Base;
    use Framework\Database\Exception as Exception;

    class Connector extends Base {

        public function initialize() {
            return $this;
        }

        protected function _getExceptionForImplementation($method) {
            return new Exception\Implementation("{$method} method not implemented");
        }

    }

} 