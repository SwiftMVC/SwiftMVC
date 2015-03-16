<?php

namespace Framework\Configuration\Driver {

    use Framework\ArrayMethods as ArrayMethods;
    use Framework\Configuration as Configuration;
    use Framework\Configuration\Exception as Exception;

    class Ini extends Configuration\Driver {

        /**
         * Parse files from ini and include them
         * 
         * @param type $path
         * @return type
         * @throws Exception\Argument
         * @throws Exception\Syntax
         */
        public function parse($path) {
            if (empty($path)) {
                throw new Exception\Argument("\$path argument is not valid");
            } if (!isset($this->_parsed[$path])) {
                $conﬁg = array();
                ob_start();
                include("{$path}.ini");
                $string = ob_get_contents();
                ob_end_clean();
                $pairs = parse_ini_string($string);
                if ($pairs == false) {
                    throw new Exception\Syntax("Could not parse Configuration ﬁle");
                } foreach ($pairs as $key => $value) {
                    $conﬁg = $this->_pair($conﬁg, $key, $value);
                } $this->_parsed[$path] = ArrayMethods::toObject($conﬁg);
            } return $this->_parsed[$path];
        }

        /**
         * It deconstructs the dot notation, used in the Configuration ﬁle’s keys,
         * into an associative array hierarchy.
         * 
         * @param type $conﬁg
         * @param type $key
         * @param type $value
         * @return type
         */
        protected function _pair($conﬁg, $key, $value) {
            if (strstr($key, ".")) {
                $parts = explode(".", $key, 2);
                if (empty($conﬁg[$parts[0]])) {
                    $conﬁg[$parts[0]] = array();
                } $conﬁg[$parts[0]] = $this->_pair($conﬁg[$parts[0]], $parts[1], $value);
            } else {
                $conﬁg[$key] = $value;
            } return $conﬁg;
        }

    }

}